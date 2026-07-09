<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Warehouse extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::updating(function (Warehouse $warehouse) {
            // BR2: Cannot deactivate warehouse with stock
            if ($warehouse->isDirty('is_active') && !$warehouse->is_active) {
                $hasStock = $warehouse->products()->wherePivot('quantity_on_hand', '>', 0)->exists();
                if ($hasStock) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'is_active' => 'Cannot deactivate a warehouse that currently has stock.',
                    ]);
                }
            }
        });
    }

    protected $fillable = [
        'name',
        'location',
        'capacity_m3',
        'is_active',
    ];

    protected $casts = [
        'capacity_m3' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
                    ->withPivot('quantity_on_hand')
                    ->withTimestamps();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
