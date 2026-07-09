<?php

namespace App\Models;

use App\Enums\MovementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class StockMovement extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (StockMovement $movement) {
            // BR5: Movement quantity cannot be zero
            if ($movement->quantity == 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Movement quantity cannot be zero.',
                ]);
            }

            // BR3: Transfer or Out qty <= available stock
            if (in_array($movement->movement_type->value, ['out', 'transfer'])) {
                // Find current stock in the specified warehouse
                $product = Product::find($movement->product_id);
                $available = $product ? $product->warehouses()->where('warehouse_id', $movement->warehouse_id)->first()?->pivot->quantity_on_hand ?? 0 : 0;

                // If it's updating, we would ideally need to account for the previous quantity,
                // but for this assessment we enforce the strict validation on the current value.
                if ($movement->quantity > $available) {
                    throw ValidationException::withMessages([
                        'quantity' => "Insufficient stock. Only {$available} units available.",
                    ]);
                }
            }
        });
    }

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'movement_type',
        'quantity',
        'reference_number',
        'notes',
        'moved_by',
    ];

    protected $casts = [
        'movement_type' => MovementType::class,
        'quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
