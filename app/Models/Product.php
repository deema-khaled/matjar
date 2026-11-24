<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatedColumns;
use App\Support\Money;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasTranslatedColumns;

    public const LOW_STOCK = 5;

    protected $fillable = [
        'category_id', 'name_en', 'name_ar', 'slug', 'description_en', 'description_ar',
        'price', 'compare_at_price', 'stock', 'image_path', 'emoji', 'is_active', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'compare_at_price' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            $product->slug = $product->slug ?: static::uniqueSlug($product->name_en);
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(4));
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Products a shopper may see and buy. @param Builder<Product> $query */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_active', true)->whereHas('category', fn (Builder $q) => $q->where('is_active', true));
    }

    public function isOnSale(): bool
    {
        return $this->compare_at_price !== null && $this->compare_at_price > $this->price;
    }

    public function discountPercent(): ?int
    {
        return $this->isOnSale() ? (int) round(100 - $this->price / $this->compare_at_price * 100) : null;
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    /** Shape used by product cards on the storefront. @return array<string, mixed> */
    public function toCard(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->translated('name'),
            'price' => Money::format($this->price),
            'compare_at_price' => $this->isOnSale() ? Money::format($this->compare_at_price) : null,
            'discount' => $this->discountPercent(),
            'image' => $this->imageUrl(),
            'emoji' => $this->emoji ?? $this->category?->icon,
            'in_stock' => $this->stock > 0,
            // How many are left, only when it's worth telling the shopper.
            'low_stock' => $this->stock > 0 && $this->stock <= self::LOW_STOCK ? $this->stock : null,
            'category' => $this->relationLoaded('category') ? $this->category?->translated('name') : null,
        ];
    }
}
