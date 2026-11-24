<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Inertia\Testing\AssertableInertia as Assert;

function customer(array $overrides = []): array
{
    return [
        'customer_name' => 'Sara Haddad',
        'phone' => '+962 79 123 4567',
        'email' => 'sara@example.com',
        'city' => 'Amman',
        'address' => 'Rainbow St 12, 2nd floor',
        ...$overrides,
    ];
}

test('checkout with an empty cart goes back to the cart', function () {
    $this->get('/checkout')->assertRedirect('/cart');
});

test('placing an order saves it, reduces stock and empties the cart', function () {
    $headphones = Product::factory()->price(20000)->stock(5)->create(['name_en' => 'Headphones']);
    $mug = Product::factory()->price(5000)->stock(10)->create();
    $this->post('/cart', ['product_id' => $headphones->id, 'quantity' => 2]);
    $this->post('/cart', ['product_id' => $mug->id]);

    $response = $this->post('/checkout', customer());

    $order = Order::with('items')->sole();
    $response->assertRedirect("/checkout/success/{$order->number}");

    expect($order->number)->toMatch('/^MTJ-[A-Z2-9]{6}$/')
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($order->subtotal)->toBe(45000)
        ->and($order->shipping)->toBe(3000)
        ->and($order->total)->toBe(48000)
        ->and($order->items)->toHaveCount(2)
        ->and($order->items->firstWhere('product_id', $headphones->id)->product_name)->toBe('Headphones')
        ->and($headphones->fresh()->stock)->toBe(3)
        ->and($mug->fresh()->stock)->toBe(9)
        ->and(session('cart'))->toBeNull();
});

test('the success page is only visible to the browser that placed the order', function () {
    $product = Product::factory()->create();
    $this->post('/cart', ['product_id' => $product->id]);
    $this->post('/checkout', customer());
    $order = Order::sole();

    $this->get("/checkout/success/{$order->number}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('store/OrderPlaced')->where('order.number', $order->number));

    $this->flushSession();
    $this->get("/checkout/success/{$order->number}")->assertNotFound();
});

test('checkout fails safely when stock ran out after adding to cart', function () {
    $product = Product::factory()->stock(3)->create();
    $this->post('/cart', ['product_id' => $product->id, 'quantity' => 3]);

    $product->update(['stock' => 1]); // someone else bought two

    $this->post('/checkout', customer())
        ->assertRedirect('/cart')
        ->assertSessionHasErrors('cart');

    expect(Order::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(1);
});

test('checkout validates the delivery details', function (array $data, string $field) {
    $this->post('/cart', ['product_id' => Product::factory()->create()->id]);

    $this->post('/checkout', customer($data))->assertSessionHasErrors($field);
})->with([
    'missing name' => [['customer_name' => ''], 'customer_name'],
    'bad phone' => [['phone' => 'call me'], 'phone'],
    'unknown city' => [['city' => 'Paris'], 'city'],
    'missing address' => [['address' => ''], 'address'],
    'bad email' => [['email' => 'nope'], 'email'],
]);

test('cancelling an order puts the stock back once', function () {
    $product = Product::factory()->stock(5)->create();
    $this->post('/cart', ['product_id' => $product->id, 'quantity' => 2]);
    $this->post('/checkout', customer());
    $order = Order::sole();

    expect($product->fresh()->stock)->toBe(3);

    $order->cancel();
    $order->cancel(); // no double restock

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($product->fresh()->stock)->toBe(5);
});
