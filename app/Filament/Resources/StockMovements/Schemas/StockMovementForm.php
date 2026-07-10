<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Enums\MovementType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->required(),
                Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->required(),
                Select::make('movement_type')
                    ->options(MovementType::class)
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->notIn([0]),
                TextInput::make('reference_number'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('moved_by')
                    ->required(),
            ]);
    }
}
