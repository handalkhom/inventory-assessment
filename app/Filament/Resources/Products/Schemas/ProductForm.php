<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->regex('/^[A-Z0-9\-]+$/')
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit'),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('unit_price')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('weight_kg')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                Select::make('category')
                    ->options(ProductCategory::class)
                    ->required(),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }
}
