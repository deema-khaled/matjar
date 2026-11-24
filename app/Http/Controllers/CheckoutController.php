<?php

namespace App\Http\Controllers;

use App\Actions\PlaceOrder;
use App\Exceptions\OutOfStockException;
use App\Models\Order;
use App\Services\Cart;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function create(Cart $cart): Response|RedirectResponse
    {
        if ($cart->count() === 0) {
            return to_route('cart.show');
        }

        return Inertia::render('store/Checkout', [
            'cart' => $cart->summary(),
            'cities' => config('store.cities'),
        ]);
    }

    public function store(Request $request, PlaceOrder $placeOrder): RedirectResponse
    {
        $customer = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^\+?[0-9\s\-()]{8,20}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['required', Rule::in(config('store.cities'))],
            'address' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $order = $placeOrder->handle($customer);
        } catch (OutOfStockException $e) {
            return to_route('cart.show')->withErrors(['cart' => $e->getMessage()]);
        }

        // Only the browser that placed the order may open its receipt page.
        $request->session()->put('placed_order', $order->number);

        return to_route('checkout.success', $order);
    }

    public function success(Request $request, Order $order): Response
    {
        abort_unless($request->session()->get('placed_order') === $order->number, 404);

        return Inertia::render('store/OrderPlaced', [
            'order' => $this->present($order->load('items.product')),
        ]);
    }

    /** @return array<string, mixed> */
    public static function present(Order $order): array
    {
        return [
            'number' => $order->number,
            'status' => $order->status->value,
            'status_label' => $order->status->getLabel(),
            'customer_name' => $order->customer_name,
            'phone' => $order->phone,
            'city' => $order->city,
            'address' => $order->address,
            'placed_on' => $order->created_at->translatedFormat('j F Y'),
            'items' => $order->items->map(fn ($item) => [
                // Current name in the shopper's language; the snapshot if the product was deleted.
                'name' => $item->product?->translated('name') ?? $item->product_name,
                'quantity' => $item->quantity,
                'line_total' => Money::format($item->line_total),
            ]),
            'subtotal' => Money::format($order->subtotal),
            'shipping' => $order->shipping === 0 ? null : Money::format($order->shipping),
            'total' => Money::format($order->total),
        ];
    }
}
