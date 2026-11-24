<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\StoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'home'])->name('home');
Route::get('shop', [StoreController::class, 'shop'])->name('shop');
Route::get('products/{product}', [StoreController::class, 'product'])->name('products.show');

Route::get('cart', [CartController::class, 'show'])->name('cart.show');
Route::post('cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('checkout', [CheckoutController::class, 'create'])->name('checkout');
Route::post('checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
Route::get('checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');

Route::get('track', OrderTrackingController::class)->middleware('throttle:30,1')->name('orders.track');

Route::post('locale/{locale}', function (Request $request, string $locale) {
    abort_unless(in_array($locale, config('store.locales'), true), 404);
    $request->session()->put('locale', $locale);

    return back();
})->name('locale');
