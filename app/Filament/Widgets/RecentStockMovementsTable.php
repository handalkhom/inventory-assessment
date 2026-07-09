<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RecentStockMovementsTable extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\StockMovement::query()
                    ->where('created_at', '>=', now()->subHours(24))
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('product.name')
                    ->label('Product'),
                \Filament\Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Warehouse'),
                \Filament\Tables\Columns\TextColumn::make('movement_type')
                    ->badge(),
                \Filament\Tables\Columns\TextColumn::make('quantity')
                    ->numeric(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
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
