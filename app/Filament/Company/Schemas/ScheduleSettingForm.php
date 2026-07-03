<?php

namespace App\Filament\Company\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ScheduleSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(self::components());
    }

    /** @return list<\Filament\Forms\Components\Component> */
    public static function components(): array
    {
        return [
            Repeater::make('days')
                ->label('Дни недели')
                ->schema([
                    TextInput::make('name')
                        ->label('День')
                        ->required()
                        ->maxLength(100),
                    Toggle::make('is_day_off')
                        ->label('Выходной')
                        ->default(false),
                    TextInput::make('day_off_text')
                        ->label('Текст выходного')
                        ->maxLength(255),
                    TextInput::make('workshop')
                        ->label('Мастерская')
                        ->maxLength(255),
                    TextInput::make('delivery')
                        ->label('Доставка')
                        ->maxLength(255),
                ])
                ->defaultItems(1)
                ->minItems(1)
                ->collapsible()
                ->collapsed()
                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                ->reorderable(),
        ];
    }
}
