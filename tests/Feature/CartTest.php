<?php

use App\Models\Product;
use Inertia\Testing\AssertableInertia as Assert;

test('products can be added to the cart', function () {
    $product = Product::factory()->price(12000)->stock(10)->create();

    $this->post('/cart', ['product_id' => $product->id, 'quantity' => 2])->assertSessionHas('success');
    $this->post('/cart', ['product_id' => $product->id])->assertSessionHas('success');

    $this->get('/cart')
        ->assertInertia(fn (Assert $page) => $page
            ->component('store/Cart')
            ->where('cartCount', 3)
            ->where('cart.lines.0.quantity', 3)
            ->where('cart.subtotal', 'JOD 36.00')
            ->where('cart.shipping', 'JOD 3.00')
            ->where('cart.total', 'JOD 39.00')
        );
});

test('quantity is capped by the stock available', function () {
    $product = Product::factory()->stock(2)->create();

    $this->post('/cart', ['product_id' => $product->id, 'quantity' => 5])
        ->assertSessionHas('success', 'Only 2 available.');

    expect(session('cart'))->toBe([$product->id => 2]);
});

test('out of stock and hidden products cannot be added', function () {
    $soldOut = Product::factory()->stock(0)->create();
    $hidden = Product::factory()->inactive()->create();

    $this->post('/cart', ['product_id' => $soldOut->id])->assertSessionHasErrors('cart');
    $this->post('/cart', ['product_id' => $hidden->id])->assertNotFound();

    expect(session('cart', []))->toBe([]);
});

test('delivery is free over the threshold', function () {
    $product = Product::factory()->price(25000)->create();

    $this->post('/cart', ['product_id' => $product->id, 'quantity' => 2]);

    $this->get('/cart')->assertInertia(fn (Assert $page) => $page
        ->where('cart.shipping', null)
        ->where('cart.total', 'JOD 50.00')
        ->where('cart.free_shipping_remaining', null)
    );
});

test('quantities can be changed and lines removed', function () {
    $a = Product::factory()->create();
    $b = Product::factory()->create();
    $this->post('/cart', ['product_id' => $a->id]);
    $this->post('/cart', ['product_id' => $b->id]);

    $this->patch("/cart/{$a->slug}", ['quantity' => 4]);
    $this->delete("/cart/{$b->slug}");

    expect(session('cart'))->toBe([$a->id => 4]);

    $this->patch("/cart/{$a->slug}", ['quantity' => 0]);
    expect(session('cart'))->toBe([]);
});

test('the cart always uses the current price', function () {
    $product = Product::factory()->price(10000)->create();
    $this->post('/cart', ['product_id' => $product->id]);

    $product->update(['price' => 15000]);

    $this->get('/cart')->assertInertia(fn (Assert $page) => $page->where('cart.subtotal', 'JOD 15.00'));
});
