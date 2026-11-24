<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Guests track an order with its number + the phone used at checkout.
 * Both must match, so order numbers alone don't leak customer details.
 */
class OrderTrackingController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $data = $request->validate([
            'number' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $order = null;
        $notFound = false;

        if (filled($data['number'] ?? null) && filled($data['phone'] ?? null)) {
            $order = Order::with('items.product')->where('number', strtoupper(trim($data['number'])))->first();

            if (! $order || ! $order->phoneMatches($data['phone'])) {
                $order = null;
                $notFound = true;
            }
        }

        return Inertia::render('store/Track', [
            'query' => ['number' => $data['number'] ?? '', 'phone' => $data['phone'] ?? ''],
            'order' => $order ? CheckoutController::present($order) : null,
            'notFound' => $notFound,
            'timeline' => collect(OrderStatus::timeline())->map(fn (OrderStatus $s) => ['value' => $s->value, 'label' => $s->getLabel()]),
        ]);
    }
}
