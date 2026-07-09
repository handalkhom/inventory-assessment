<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => 'WH-' . $this->faker->numberBetween(100, 999) . ' ' . $this->faker->city(),
            'location' => $this->faker->city() . ', Indonesia',
            'capacity_m3' => $this->faker->randomFloat(2, 1000, 50000),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
