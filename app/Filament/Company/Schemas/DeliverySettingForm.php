<?php

namespace App\Filament\Company\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DeliverySettingForm
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
            Repeater::make('free_conditions')
                ->label('Условия бесплатной доставки')
                ->simple(
                    TextInput::make('text')
                        ->label('Условие')
                        ->required()
                        ->maxLength(500),
                )
                ->defaultItems(1)
                ->collapsible()
                ->collapsed(),
            Repeater::make('advantages')
                ->label('Преимущества')
                ->schema([
                    TextInput::make('title')
                        ->label('Заголовок')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label('Описание')
                        ->required()
                        ->rows(3)
                        ->maxLength(1000),
                ])
                ->defaultItems(1)
                ->collapsible()
                ->collapsed()
                ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                ->reorderable(),
        ];
    }
}
