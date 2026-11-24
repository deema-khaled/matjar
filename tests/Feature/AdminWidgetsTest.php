<?php

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Widgets\LatestOrders;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\StoreStats;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the stats widget sums this month\'s sales and ignores cancelled orders', function () {
    Order::factory()->create(['total' => 40000]);
    Order::factory()->create(['total' => 20000]);
    Order::factory()->status(OrderStatus::Cancelled)->create(['total' => 99000]);
    Product::factory()->stock(2)->create();

    Livewire::test(StoreStats::class)
        ->assertSee('JOD 60.00')       // revenue
        ->assertSee('Low stock');
});

test('the revenue chart and latest orders render', function () {
    $order = Order::factory()->create();

    Livewire::test(RevenueChart::class)->assertOk();
    Livewire::test(LatestOrders::class)->assertSee($order->number);
});

test('an admin can advance and cancel an order from its page', function () {
    $product = Product::factory()->stock(5)->create();
    $order = Order::factory()->create();
    $order->items()->create([
        'product_id' => $product->id, 'product_name' => $product->name_en,
        'unit_price' => $product->price, 'quantity' => 2, 'line_total' => $product->price * 2,
    ]);

    Livewire::test(ViewOrder::class, ['record' => $order->number])
        ->callAction('advance');
    expect($order->fresh()->status)->toBe(OrderStatus::Processing);

    Livewire::test(ViewOrder::class, ['record' => $order->number])
        ->callAction('cancel');
    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($product->fresh()->stock)->toBe(7);
});
