<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

class ManageApiTokens extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';
    protected static string|null $navigationLabel = 'API Tokens';
protected static string|null $title = 'Manage API Tokens';

    protected string $view = 'filament.pages.manage-api-tokens';
    
    public ?string $generatedToken = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateToken')
                ->label('Generate New API Token')
                ->form([
                    TextInput::make('name')
                        ->label('Token Name')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    $token = auth()->user()->createToken($data['name']);
                    $this->generatedToken = $token->plainTextToken;
                }),
        ];
    }
}
