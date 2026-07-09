<?php

namespace App\Filament\Resources\StockMovements\Widgets;

use App\Models\StockMovement;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockMovementStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $todayMovements = StockMovement::whereDate('created_at', today())->count();
        $inbound = StockMovement::where('movement_type', 'in')->sum('quantity');
        $outbound = StockMovement::where('movement_type', 'out')->sum('quantity');

        return [
            Stat::make('Total Movements Today', $todayMovements),
            Stat::make('Inbound Quantity', $inbound)
                ->description('Total incoming stock')
                ->color('success'),
            Stat::make('Outbound Quantity', abs($outbound))
                ->description('Total outgoing stock')
                ->color('danger'),
        ];
    }
}
