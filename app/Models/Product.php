<?php

namespace App\Models;

use App\Enums\ProductCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;


class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'unit_price',
        'weight_kg',
        'category',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'category' => ProductCategory::class,
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Enforce BR1: SKU is unique and immutable. We prevent modification after creation.
        static::updating(function (Product $product) {
            if ($product->isDirty('sku')) {
                throw new \InvalidArgumentException('SKU is immutable and cannot be changed.');
            }
        });
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class)
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
