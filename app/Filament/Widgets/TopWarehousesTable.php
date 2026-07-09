<?php

namespace App\Filament\Widgets;

use App\Models\Warehouse;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Model;

class TopWarehousesTable extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        /*
         * ASSUMPTION:
         * For the purpose of this assessment, capacity utilization is calculated
         * using the current quantity_on_hand relative to warehouse capacity
         * because the provided data model does not contain product volume information.
         */
        return $table
            ->query(
                Warehouse::query()
                    ->withSum('products as total_stock', 'product_warehouse.quantity_on_hand')
                    ->orderByRaw('(IFNULL(products_sum_product_warehousequantity_on_hand, total_stock) / capacity_m3) DESC')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Warehouse'),
                TextColumn::make('capacity_m3')
                    ->label('Capacity (m³)')
                    ->numeric(),
                TextColumn::make('total_stock')
                    ->label('Total Stock (Qty)')
                    ->numeric(),
                TextColumn::make('utilization')
                    ->label('Utilization')
                    ->state(function ($record) {
                        $stock = $record->total_stock ?? 0;
                        $cap = $record->capacity_m3 ?: 1; // avoid division by zero

                        return number_format(($stock / $cap) * 100, 2).'%';
                    }),
            ])
            ->paginated(false)
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
