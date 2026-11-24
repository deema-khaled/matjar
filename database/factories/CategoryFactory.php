<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word());

        return [
            'name_en' => $name,
            'name_ar' => 'فئة '.$name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'icon' => fake()->randomElement(['🎧', '👟', '⌚', '👜', '🏠', '☕']),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
