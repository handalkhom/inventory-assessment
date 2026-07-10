<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshStockSummaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:refresh-summaries';

    protected $description = 'Refresh warehouse stock summaries for fast reporting';

    public function handle()
    {
        $this->info('Refreshing warehouse stock summaries...');
        
        \Illuminate\Support\Facades\DB::table('warehouse_stock_summaries')->truncate();

        $summaries = \App\Models\Warehouse::select('warehouses.id')
            ->leftJoin('product_warehouse', 'warehouses.id', '=', 'product_warehouse.warehouse_id')
            ->leftJoin('products', 'product_warehouse.product_id', '=', 'products.id')
            ->groupBy('warehouses.id')
            ->selectRaw('COALESCE(SUM(products.unit_price * product_warehouse.quantity_on_hand), 0) as total_stock_value')
            ->get();

        $insertData = [];
        $now = now();
        foreach ($summaries as $summary) {
            $insertData[] = [
                'warehouse_id' => $summary->id,
                'total_stock_value' => $summary->total_stock_value,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        \Illuminate\Support\Facades\DB::table('warehouse_stock_summaries')->insert($insertData);

        $this->info('Stock summaries refreshed successfully.');
    }
}
