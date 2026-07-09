<?php

use App\Livewire\StockAdjustmentManager;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
use App\Enums\MovementType;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(StockAdjustmentManager::class)
        ->assertStatus(200);
});

it('validates required fields on submit', function () {
    Livewire::test(StockAdjustmentManager::class)
        ->call('submit')
        ->assertHasErrors([
            'productId' => 'required',
            'warehouseId' => 'required',
        ]);
});

it('validates quantity exceeds available stock', function () {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();
    // Attach with 5 stock
    $product->warehouses()->attach($warehouse, ['quantity_on_hand' => 5]);

    Livewire::test(StockAdjustmentManager::class)
        ->set('productId', $product->id)
        ->set('warehouseId', $warehouse->id)
        ->set('quantity', 10)
        ->call('submit')
        ->assertHasErrors(['quantity']);
});

it('successfully submits a stock adjustment', function () {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();
    // Attach with 10 stock
    $product->warehouses()->attach($warehouse, ['quantity_on_hand' => 10]);

    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(StockAdjustmentManager::class)
        ->set('productId', $product->id)
        ->set('warehouseId', $warehouse->id)
        ->set('quantity', 3)
        ->set('notes', 'Damaged items')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertDispatched('stock-adjusted');

    // Verify stock was reduced by 3
    $this->assertDatabaseHas('product_warehouse', [
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity_on_hand' => 7,
    ]);

    // Verify movement was logged
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'movement_type' => MovementType::ADJUSTMENT->value,
        'quantity' => -3,
        'notes' => 'Damaged items',
    ]);
});
