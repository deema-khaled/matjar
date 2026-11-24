<?php

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ProductResource;
use App\Models\Order;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

// --- Order tracking ------------------------------------------------------------

test('an order can be tracked with its number and phone', function () {
    $order = Order::factory()->status(OrderStatus::Shipped)->create(['phone' => '+962791234567']);

    // Phone formatting doesn't matter, and the number is case-insensitive.
    $this->get('/track?'.http_build_query(['number' => strtolower($order->number), 'phone' => '+962 79-123 4567']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('store/Track')
            ->where('order.number', $order->number)
            ->where('order.status', 'shipped')
            ->where('notFound', false)
        );
});

test('tracking with the wrong phone reveals nothing', function () {
    $order = Order::factory()->create(['phone' => '+962791234567']);

    $this->get('/track?'.http_build_query(['number' => $order->number, 'phone' => '+962780000000']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('order', null)
            ->where('notFound', true)
        );
});

test('the tracking page starts empty', function () {
    $this->get('/track')->assertInertia(fn (Assert $page) => $page->where('order', null)->where('notFound', false));
});

// --- Admin panel ------------------------------------------------------------------

test('guests are sent to the admin login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('non-admin users cannot open the admin panel', function () {
    $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
});

test('admins can open the dashboard and resources', function () {
    $admin = User::factory()->admin()->create();
    Order::factory()->count(3)->create();

    $this->actingAs($admin)->get('/admin')->assertOk();
    $this->actingAs($admin)->get(ProductResource::getUrl('index'))->assertOk();
    $this->actingAs($admin)->get(OrderResource::getUrl('index'))->assertOk();
    $this->actingAs($admin)->get(OrderResource::getUrl('view', ['record' => Order::first()]))->assertOk();
});
