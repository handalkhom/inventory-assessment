<?php

namespace Database\Factories;

use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Enums\MovementType;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(MovementType::cases());
        $quantity = $this->faker->numberBetween(1, 500);
        
        if ($type === MovementType::OUT) {
            $quantity = -$quantity;
        } elseif ($type === MovementType::TRANSFER || $type === MovementType::ADJUSTMENT) {
            $quantity = $this->faker->boolean() ? $quantity : -$quantity;
        }

        return [
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'movement_type' => $type,
            'quantity' => $quantity,
            'reference_number' => strtoupper($this->faker->bothify('??-2026-####')),
            'notes' => $this->faker->optional()->sentence(),
            'moved_by' => $this->faker->userName(),
        ];
    }

    public function forProduct(Product $product): self
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    public function forWarehouse(Warehouse $warehouse): self
    {
        return $this->state(fn (array $attributes) => [
            'warehouse_id' => $warehouse->id,
        ]);
    }
}
