<?php

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Validation\ValidationException;

it('cannot deactivate a warehouse with stock', function () {
    $warehouse = Warehouse::factory()->create(['is_active' => true]);
    $product = Product::factory()->create();

    // Attach product with stock to warehouse
    $warehouse->products()->attach($product->id, ['quantity_on_hand' => 10]);

    // Attempting to deactivate should throw validation exception
    expect(fn () => $warehouse->update(['is_active' => false]))
        ->toThrow(ValidationException::class, 'Cannot deactivate a warehouse that currently has stock.');
});

it('can deactivate a warehouse without stock', function () {
    $warehouse = Warehouse::factory()->create(['is_active' => true]);
    $product = Product::factory()->create();

    // Attach product with NO stock (0 quantity)
    $warehouse->products()->attach($product->id, ['quantity_on_hand' => 0]);

    // Deactivating should succeed
    $warehouse->update(['is_active' => false]);
    expect($warehouse->fresh()->is_active)->toBeFalse();
});
