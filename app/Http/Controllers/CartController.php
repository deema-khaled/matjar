<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(private readonly Cart $cart) {}

    public function show(): Response
    {
        return Inertia::render('store/Cart', ['cart' => $this->cart->summary()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:'.config('store.max_quantity_per_item')],
        ]);

        $product = Product::visible()->findOrFail($data['product_id']);

        if ($product->stock < 1) {
            return back()->withErrors(['cart' => __('store.out_of_stock')]);
        }

        $before = $this->cart->quantityOf($product);
        $wanted = $before + ($data['quantity'] ?? 1);
        $after = $this->cart->add($product, $data['quantity'] ?? 1);

        if ($after < $wanted) {
            return back()->with('success', __('store.max_reached', ['count' => $after]));
        }

        return back()->with('success', __('store.added_to_cart', ['product' => $product->translated('name')]));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:'.config('store.max_quantity_per_item')]]);

        $this->cart->set($product, $data['quantity']);

        return back();
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->cart->remove($product);

        return back();
    }
}
