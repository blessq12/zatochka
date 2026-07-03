<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;

final class EquipmentSerialNumbersFormField
{
    public static function repeater(string $name = 'serial_numbers'): Repeater
    {
        return Repeater::make($name)
            ->label('Серийные номера')
            ->schema([
                TextInput::make('component')
                    ->label('Компонент')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('ручка'),
                TextInput::make('serial')
                    ->label('Серийный номер')
                    ->required()
                    ->maxLength(100),
            ])
            ->columns(2)
            ->defaultItems(0)
            ->addActionLabel('Добавить компонент')
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => filled($state['component'] ?? null) ? $state['component'] : null)
            ->columnSpanFull();
    }
}
