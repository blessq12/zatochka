<?php

namespace App\Filament\Resources\Equipment\Schemas;

use App\Filament\Support\EquipmentSerialNumbersFormField;
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
                EquipmentSerialNumbersFormField::repeater(),
            ]);
    }
}
