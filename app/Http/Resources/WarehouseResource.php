<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'capacity_m3' => $this->capacity_m3,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'products' => $this->whenLoaded('products', function () {
                return $this->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'quantity_on_hand' => $product->pivot->quantity_on_hand,
                    ];
                });
            }),
        ];
    }
}
