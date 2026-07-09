<?php

namespace Database\Factories;

use App\Enums\ProductCategory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'sku' => strtoupper($this->faker->lexify('PROD-??????').'-'.$this->faker->numerify('####')),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'unit_price' => $this->faker->randomFloat(2, 0.50, 2500),
            'weight_kg' => $this->faker->randomFloat(2, 0.01, 500),
            'category' => $this->faker->randomElement(ProductCategory::cases()),
            'is_active' => $this->faker->boolean(95),
        ];
    }
}
