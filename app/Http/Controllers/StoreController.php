<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('store/Home', [
            'categories' => Category::active()->withCount(['products' => fn ($q) => $q->where('is_active', true)])->get()
                ->map(fn (Category $c) => [...$c->toStorefront(), 'count' => $c->products_count]),
            'featured' => Product::visible()->with('category')->where('is_featured', true)->latest()->limit(8)->get()
                ->map->toCard(),
            'freeShippingOver' => Money::format(config('store.shipping.free_over')),
        ]);
    }

    public function shop(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc'])],
            'in_stock' => ['nullable', 'boolean'],
        ]);

        $search = trim($filters['q'] ?? '');
        $category = isset($filters['category']) ? Category::active()->where('slug', $filters['category'])->first() : null;

        $products = Product::visible()
            ->with('category')
            ->when($category, fn (Builder $q) => $q->whereBelongsTo($category))
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w
                ->whereLike('name_en', "%{$search}%")
                ->orWhereLike('name_ar', "%{$search}%")
                ->orWhereLike('description_en', "%{$search}%")))
            ->when($filters['in_stock'] ?? false, fn (Builder $q) => $q->where('stock', '>', 0))
            ->when(
                $filters['sort'] ?? 'newest',
                fn (Builder $q, string $sort) => match ($sort) {
                    'price_asc' => $q->orderBy('price'),
                    'price_desc' => $q->orderByDesc('price'),
                    default => $q->latest('id'),
                },
            )
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Product $p) => $p->toCard());

        return Inertia::render('store/Shop', [
            'products' => $products,
            'categories' => Category::active()->get()->map->toStorefront(),
            'activeCategory' => $category?->toStorefront(),
            'filters' => [
                'q' => $search,
                'category' => $category?->slug,
                'sort' => $filters['sort'] ?? 'newest',
                'in_stock' => (bool) ($filters['in_stock'] ?? false),
            ],
        ]);
    }

    public function product(Product $product): Response
    {
        // Hidden products (or products in hidden categories) behave as if they don't exist.
        abort_unless(Product::visible()->whereKey($product->id)->exists(), 404);

        $product->load('category');

        return Inertia::render('store/Product', [
            'product' => [
                ...$product->toCard(),
                'description' => $product->translated('description'),
                'stock' => $product->stock,
                'max_quantity' => min($product->stock, config('store.max_quantity_per_item')),
                'category' => $product->category->toStorefront(),
            ],
            'related' => Product::visible()->with('category')
                ->whereBelongsTo($product->category)
                ->whereKeyNot($product->id)
                ->inRandomOrder()
                ->limit(4)
                ->get()
                ->map->toCard(),
        ]);
    }
}
