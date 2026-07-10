<?php

use App\Enums\MovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Validation\ValidationException;

it('rejects a stock movement with zero quantity', function () {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();

    expect(fn () => StockMovement::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 0,
        'movement_type' => MovementType::IN,
    ]))->toThrow(ValidationException::class, 'Movement quantity cannot be zero.');
});

it('rejects an outbound stock movement exceeding available stock', function () {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    // Set available stock to 50
    $warehouse->products()->attach($product->id, ['quantity_on_hand' => 50]);

    expect(fn () => StockMovement::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'movement_type' => MovementType::OUT,
        'quantity' => 60, // Exceeds 50
    ]))->toThrow(ValidationException::class, 'Insufficient stock. Only 50 units available.');
});

it('rejects a transfer stock movement exceeding available stock', function () {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    // Set available stock to 20
    $warehouse->products()->attach($product->id, ['quantity_on_hand' => 20]);

    expect(fn () => StockMovement::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'movement_type' => MovementType::TRANSFER,
        'quantity' => 21, // Exceeds 20
    ]))->toThrow(ValidationException::class, 'Insufficient stock. Only 20 units available.');
});
