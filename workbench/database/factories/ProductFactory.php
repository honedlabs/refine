<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Workbench\App\Enums\Status;
use Workbench\App\Models\Product;

/**
 * @template TModel of \Workbench\App\Models\Product
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<TModel>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => Str::uuid(),
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'price' => fake()->randomNumber(4),
            'best_seller' => fake()->boolean(),
            'status' => fake()->randomElement(Status::cases()),
            'created_at' => now()->subDays(fake()->randomNumber(2)),
        ];
    }
}
