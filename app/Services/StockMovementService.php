<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    public function createMovement(array $data): StockMovement
    {
        return DB::transaction(function () use ($data) {
            $product = Product::where('sku', $data['product_sku'])->firstOrFail();
            $warehouseId = $data['warehouse_id'];
            $movementType = $data['movement_type'];
            $quantity = (int) $data['quantity'];

            // Create the movement. The StockMovement model event will validate BR3 and BR5
            // and throw ValidationException if rules are violated.
            $movement = StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'moved_by' => $data['moved_by'],
            ]);

            // Update the pivot table (quantity_on_hand)
            $currentStock = $product->warehouses()->where('warehouse_id', $warehouseId)->first()?->pivot->quantity_on_hand ?? 0;

            // Determine if we need to add or subtract from stock based on movement_type
            if (in_array($movementType, ['in', 'adjustment'])) {
                $newStock = $currentStock + $quantity;
            } else {
                // 'out' or 'transfer'
                $newStock = $currentStock - $quantity;
            }

            if ($product->warehouses()->where('warehouse_id', $warehouseId)->exists()) {
                $product->warehouses()->updateExistingPivot($warehouseId, ['quantity_on_hand' => $newStock]);
            } else {
                $product->warehouses()->attach($warehouseId, ['quantity_on_hand' => $newStock]);
            }

            return $movement->load(['product', 'warehouse']);
        });
    }
}
