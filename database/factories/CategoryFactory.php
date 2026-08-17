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
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'seo_title' => fake()->optional()->sentence(6),
            'seo_description' => fake()->optional()->sentence(12),
            'status' => 'approved',
            'created_by' => null,
            'moderation_reason' => null,
            'is_active' => true,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'is_active' => false,
        ]);
    }

    public function rejected(string $reason = 'Rejected by moderation'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'is_active' => false,
            'moderation_reason' => $reason,
        ]);
    }
}
