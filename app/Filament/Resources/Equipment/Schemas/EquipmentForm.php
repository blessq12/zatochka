<?php

namespace App\Filament\Resources\Equipment\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EquipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                TextInput::make('brand')
                    ->label('Бренд')
                    ->maxLength(255),
                TextInput::make('model')
                    ->label('Модель')
                    ->maxLength(255),
                Repeater::make('serial_numbers')
                    ->label('Серийные номера')
                    ->simple(
                        TextInput::make('value')
                            ->label('Серийный номер')
                            ->required()
                            ->maxLength(100),
                    )
                    ->defaultItems(0)
                    ->collapsible(),
            ]);
    }
}
