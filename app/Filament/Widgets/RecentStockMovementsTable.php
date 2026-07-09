<?php

namespace App\Filament\Widgets;

use App\Models\StockMovement;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentStockMovementsTable extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StockMovement::query()
                    ->where('created_at', '>=', now()->subHours(24))
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product'),
                TextColumn::make('warehouse.name')
                    ->label('Warehouse'),
                TextColumn::make('movement_type')
                    ->badge(),
                TextColumn::make('quantity')
                    ->numeric(),
                TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime(),
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
