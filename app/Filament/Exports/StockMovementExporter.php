<?php

namespace App\Filament\Exports;

use App\Models\StockMovement;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class StockMovementExporter extends Exporter
{
    protected static ?string $model = StockMovement::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('product.name')->label('Product'),
            ExportColumn::make('warehouse.name')->label('Warehouse'),
            ExportColumn::make('movement_type')->label('Type'),
            ExportColumn::make('quantity')->label('Quantity'),
            ExportColumn::make('reference_number')->label('Reference Number'),
            ExportColumn::make('moved_by')->label('Moved By'),
            ExportColumn::make('created_at')->label('Date'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your stock movement export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
