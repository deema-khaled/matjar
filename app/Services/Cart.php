<?php

namespace App\Services;

use App\Models\Product;
use App\Support\Money;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Guest shopping cart kept in the session as [product_id => quantity].
 * Prices are never stored in the cart: they are always read from the
 * database, so a price change can't be bypassed by an old session.
 */
class Cart
{
    private const KEY = 'cart';

    public function __construct(private readonly Session $session) {}

    /** @return array<int, int> */
    public function raw(): array
    {
        return $this->session->get(self::KEY, []);
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function quantityOf(Product $product): int
    {
        return $this->raw()[$product->id] ?? 0;
    }

    /** Adds to the existing quantity, capped by stock and the per-item limit. Returns the new quantity. */
    public function add(Product $product, int $quantity = 1): int
    {
        return $this->set($product, $this->quantityOf($product) + $quantity);
    }

    public function set(Product $product, int $quantity): int
    {
        $items = $this->raw();
        $quantity = max(0, min($quantity, $product->stock, config('store.max_quantity_per_item')));

        if ($quantity === 0) {
            unset($items[$product->id]);
        } else {
            $items[$product->id] = $quantity;
        }

        $this->session->put(self::KEY, $items);

        return $quantity;
    }

    public function remove(Product $product): void
    {
        $this->set($product, 0);
    }

    public function clear(): void
    {
        $this->session->forget(self::KEY);
    }

    /**
     * Cart lines with fresh prices. Products that were deleted or hidden
     * since they were added are dropped.
     *
     * @return Collection<int, array{product: Product, quantity: int, line_total: int}>
     */
    public function lines(): Collection
    {
        $items = $this->raw();

        if ($items === []) {
            return collect();
        }

        $products = Product::visible()->with('category')->whereKey(array_keys($items))->get()->keyBy('id');

        return collect($items)
            ->filter(fn ($qty, $id) => $products->has($id))
            ->map(fn (int $qty, int $id) => [
                'product' => $products[$id],
                'quantity' => $qty,
                'line_total' => $products[$id]->price * $qty,
            ])
            ->values();
    }

    /** @return array{subtotal: int, shipping: int, total: int, free_shipping_remaining: int} */
    public static function totals(int $subtotal): array
    {
        $freeOver = config('store.shipping.free_over');
        $shipping = $subtotal === 0 || $subtotal >= $freeOver ? 0 : config('store.shipping.flat_rate');

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
            'free_shipping_remaining' => max(0, $freeOver - $subtotal),
        ];
    }

    /** Everything the cart page and checkout need, already formatted. @return array<string, mixed> */
    public function summary(): array
    {
        $lines = $this->lines();
        $totals = self::totals($lines->sum('line_total'));

        return [
            'lines' => $lines->map(fn (array $line) => [
                ...$line['product']->toCard(),
                'quantity' => $line['quantity'],
                'max_quantity' => min($line['product']->stock, config('store.max_quantity_per_item')),
                'unit_price' => Money::format($line['product']->price),
                'line_total' => Money::format($line['line_total']),
            ]),
            'count' => $lines->sum('quantity'),
            'subtotal' => Money::format($totals['subtotal']),
            'shipping' => $totals['shipping'] === 0 ? null : Money::format($totals['shipping']),
            'total' => Money::format($totals['total']),
            'free_shipping_remaining' => $totals['free_shipping_remaining'] > 0 ? Money::format($totals['free_shipping_remaining']) : null,
            'free_shipping_progress' => min(100, (int) round($totals['subtotal'] / config('store.shipping.free_over') * 100)),
        ];
    }
}
