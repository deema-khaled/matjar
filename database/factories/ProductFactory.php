<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = ucwords(fake()->unique()->words(2, true));

        return [
            'category_id' => Category::factory(),
            'name_en' => $name,
            'name_ar' => 'منتج '.$name,
            'description_en' => fake()->sentence(12),
            'description_ar' => 'وصف المنتج باللغة العربية.',
            'price' => fake()->numberBetween(5, 120) * 1000,
            'stock' => fake()->numberBetween(5, 40),
            'is_active' => true,
        ];
    }

    /** @param int $price in fils */
    public function price(int $price, ?int $compareAt = null): static
    {
        return $this->state(['price' => $price, 'compare_at_price' => $compareAt]);
    }

    public function stock(int $stock): static
    {
        return $this->state(['stock' => $stock]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }
}
