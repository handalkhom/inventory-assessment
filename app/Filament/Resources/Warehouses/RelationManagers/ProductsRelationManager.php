<?php

namespace App\Filament\Resources\Warehouses\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('sku')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('quantity_on_hand')
                    ->label('Stock Quantity')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            // The rest is Read-only
            ->headerActions([
            ])
            ->recordActions([
            ])
            ->toolbarActions([
            ]);
    }
}
