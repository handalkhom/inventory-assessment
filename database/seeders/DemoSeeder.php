<?php

namespace Database\Seeders;

use App\Enums\MovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Create 5 warehouses
            $warehouses = Warehouse::factory()->count(5)->create();

            // 2. Create 50 products
            $products = Product::factory()->count(50)->create();

            // 3. Attach each product to 1-3 random warehouses
            foreach ($products as $product) {
                $randomWarehouses = $warehouses->random(rand(1, 3));
                foreach ($randomWarehouses as $warehouse) {
                    $product->warehouses()->attach($warehouse->id, [
                        'quantity_on_hand' => rand(1, 999),
                    ]);
                }
            }

            // Reload products with warehouses relation eager loaded
            $products->load('warehouses');

            $movementsData = [];
            $faker = Factory::create();
            $cases = MovementType::cases();

            $now = now();

            for ($i = 0; $i < 200; $i++) {
                $product = $products->random();
                $warehouse = $product->warehouses->count() > 0
                                ? $product->warehouses->random()
                                : $warehouses->random();

                $type = $faker->randomElement($cases);
                $quantity = $faker->numberBetween(1, 500);

                if ($type === MovementType::OUT) {
                    $quantity = -$quantity;
                } elseif ($type === MovementType::TRANSFER || $type === MovementType::ADJUSTMENT) {
                    $quantity = $faker->boolean() ? $quantity : -$quantity;
                }

                $movementsData[] = [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'movement_type' => $type->value,
                    'quantity' => $quantity,
                    'reference_number' => strtoupper($faker->bothify('??-2026-####')),
                    'notes' => $faker->optional()->sentence(),
                    'moved_by' => $faker->userName(), // Updated per point 4 (faker username)
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Batch insert
            StockMovement::insert($movementsData);
        });
    }
}
