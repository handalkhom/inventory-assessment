<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;

class StockReportController extends Controller
{
    /**
     * Get aggregated stock value per warehouse.
     */
    public function index()
    {
        $report = Warehouse::select('warehouses.id', 'warehouses.name')
            ->leftJoin('product_warehouse', 'warehouses.id', '=', 'product_warehouse.warehouse_id')
            ->leftJoin('products', 'product_warehouse.product_id', '=', 'products.id')
            ->groupBy('warehouses.id', 'warehouses.name')
            ->selectRaw('COALESCE(SUM(products.unit_price * product_warehouse.quantity_on_hand), 0) as total_stock_value')
            ->get()
            ->map(function ($warehouse) {
                // Format total_stock_value as a number (it comes back as string from some DB drivers)
                return [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'total_stock_value' => (float) $warehouse->total_stock_value,
                ];
            });

        return response()->json($report);
    }
}
