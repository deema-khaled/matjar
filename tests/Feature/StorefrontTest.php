<?php

use App\Models\Category;
use App\Models\Product;
use Inertia\Testing\AssertableInertia as Assert;

test('the home page shows active categories and featured products', function () {
    $category = Category::factory()->create();
    Product::factory()->for($category)->featured()->create();
    Product::factory()->for($category)->create(); // not featured
    Category::factory()->inactive()->create();

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('store/Home')
            ->has('categories', 1)
            ->has('featured', 1)
        );
});

test('the shop hides inactive products and products in hidden categories', function () {
    $visible = Product::factory()->create();
    Product::factory()->inactive()->create();
    Product::factory()->for(Category::factory()->inactive())->create();

    $this->get('/shop')
        ->assertInertia(fn (Assert $page) => $page
            ->component('store/Shop')
            ->where('products.total', 1)
            ->where('products.data.0.id', $visible->id)
        );
});

test('the shop can be filtered by category and search', function () {
    $audio = Category::factory()->create(['slug' => 'audio']);
    Product::factory()->for($audio)->create(['name_en' => 'Wireless Headphones']);
    Product::factory()->for($audio)->create(['name_en' => 'Speaker']);
    Product::factory()->create(['name_en' => 'Wireless Mouse']);

    $this->get('/shop?category=audio')->assertInertia(fn (Assert $page) => $page->where('products.total', 2));
    $this->get('/shop?q=wireless')->assertInertia(fn (Assert $page) => $page->where('products.total', 2));
    $this->get('/shop?category=audio&q=wireless')->assertInertia(fn (Assert $page) => $page->where('products.total', 1));
});

test('the shop can be sorted by price and limited to stock', function () {
    Product::factory()->price(30000)->create();
    Product::factory()->price(10000)->stock(0)->create();
    Product::factory()->price(20000)->create();

    $this->get('/shop?sort=price_asc')
        ->assertInertia(fn (Assert $page) => $page->where('products.data.0.price', 'JOD 10.00'));

    $this->get('/shop?sort=price_desc&in_stock=1')
        ->assertInertia(fn (Assert $page) => $page
            ->where('products.total', 2)
            ->where('products.data.0.price', 'JOD 30.00')
        );
});

test('a product page shows details and related products', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->price(45000, 60000)->stock(3)->create(['slug' => 'nice-bag']);
    Product::factory()->for($category)->count(2)->create();

    $this->get('/products/nice-bag')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('store/Product')
            ->where('product.id', $product->id)
            ->where('product.discount', 25)
            ->where('product.low_stock', 3)
            ->has('related', 2)
        );
});

test('hidden products return 404', function () {
    $product = Product::factory()->inactive()->create();

    $this->get("/products/{$product->slug}")->assertNotFound();
});

test('the store switches to Arabic', function () {
    Product::factory()->create(['name_en' => 'Backpack', 'name_ar' => 'حقيبة ظهر']);

    $this->post('/locale/ar')->assertRedirect();

    $this->get('/shop')
        ->assertSee('dir="rtl"', false)
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'ar')
            ->where('translations.nav_shop', 'المتجر')
            ->where('products.data.0.name', 'حقيبة ظهر')
            ->where('products.data.0.price', fn ($price) => str_contains($price, 'د.أ'))
        );
});

test('unknown locales are rejected', function () {
    $this->post('/locale/fr')->assertNotFound();
});
