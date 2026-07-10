<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;

class StockReportController extends Controller
{    
    // Get aggregated stock
    public function index()
    {
        $report = Warehouse::select('warehouses.id', 'warehouses.name')
            ->leftJoin('warehouse_stock_summaries as wss', 'warehouses.id', '=', 'wss.warehouse_id')
            ->selectRaw('COALESCE(wss.total_stock_value, 0) as total_stock_value')
            ->get()
            ->map(function ($warehouse) {
                return [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'total_stock_value' => (float) $warehouse->total_stock_value,
                ];
            });

        return response()->json($report);
    }
}
