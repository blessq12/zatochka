<?php

namespace App\Filament\Resources\WarehouseItems\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WarehouseItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')
                    ->label('Артикул')
                    ->required()
                    ->maxLength(64)
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                TextInput::make('unit')
                    ->label('Единица измерения')
                    ->required()
                    ->maxLength(16)
                    ->placeholder('шт'),
                TextInput::make('price')
                    ->label('Цена')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
            ]);
    }
}
