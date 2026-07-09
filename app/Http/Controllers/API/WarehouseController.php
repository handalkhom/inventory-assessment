<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;

class WarehouseController extends Controller
{
    /**
     * Display all products and their quantities in a specific warehouse.
     */
    public function stock($id)
    {
        $warehouse = Warehouse::with('products')->findOrFail($id);

        return new WarehouseResource($warehouse);
    }
}
