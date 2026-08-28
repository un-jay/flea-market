<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'item_name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(100, 100000),
            'brand_name' => fake()->company(),
            'status' => 1,
            'item_image' => 'storage/images/dummy.jpg',
            'is_sold' => false,
        ];
    }

    public function sold()
    {
        return $this->state(fn (array $attributes) => [
            'is_sold' => true,
        ]);
    }
}
