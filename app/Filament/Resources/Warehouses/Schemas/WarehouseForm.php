<?php

namespace App\Filament\Resources\Warehouses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->minLength(3)
                    ->maxLength(100),
                TextInput::make('location')
                    ->required()
                    ->maxLength(255),
                TextInput::make('capacity_m3')
                    ->required()
                    ->numeric()
                    ->rule('gt:0'),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }
}
