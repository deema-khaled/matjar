<?php

namespace App\Actions;

use App\Exceptions\OutOfStockException;
use App\Models\Order;
use App\Models\Product;
use App\Services\Cart;
use Illuminate\Support\Facades\DB;

/**
 * Turns the cart into an order, atomically:
 *  - locks the product rows (SELECT ... FOR UPDATE) so two shoppers can't
 *    both buy the last item,
 *  - re-checks stock and prices from the database,
 *  - decrements stock and stores a price snapshot on each order line.
 */
class PlaceOrder
{
    public function __construct(private readonly Cart $cart) {}

    /**
     * @param  array{customer_name: string, phone: string, email?: ?string, city: string, address: string, notes?: ?string}  $customer
     *
     * @throws OutOfStockException
     */
    public function handle(array $customer): Order
    {
        $items = $this->cart->raw();

        abort_if($items === [], 422, 'Your cart is empty.');

        $order = DB::transaction(function () use ($items, $customer) {
            $products = Product::visible()
                ->whereKey(array_keys($items))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = [];
            foreach ($items as $productId => $quantity) {
                $product = $products->get($productId);

                if (! $product || $product->stock < $quantity) {
                    throw new OutOfStockException($product, $product?->stock ?? 0);
                }

                $lines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'line_total' => $product->price * $quantity,
                ];
            }

            $totals = Cart::totals(array_sum(array_column($lines, 'line_total')));

            $order = Order::create([
                ...$customer,
                ...collect($totals)->only(['subtotal', 'shipping', 'total'])->all(),
                'locale' => app()->getLocale(),
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name_en,
                    'unit_price' => $line['product']->price,
                    'quantity' => $line['quantity'],
                    'line_total' => $line['line_total'],
                ]);

                $line['product']->decrement('stock', $line['quantity']);
            }

            return $order;
        });

        $this->cart->clear();

        return $order;
    }
}
