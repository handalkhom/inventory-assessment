<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $activeProducts = \App\Models\Product::where('is_active', true)->count();
        
        return [
            Stat::make('Total Active Products', $activeProducts)
                ->description('Products currently active in the system')
                ->color('primary'),
        ];
    }
}
