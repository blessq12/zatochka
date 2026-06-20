<?php

namespace App\Filament\SiteSettings\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FaqSettingForm
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
            Repeater::make('items')
                ->label('Вопросы')
                ->schema([
                    TextInput::make('question')
                        ->label('Вопрос')
                        ->required()
                        ->maxLength(500),
                    Repeater::make('answer_lines')
                        ->label('Строки ответа')
                        ->simple(
                            TextInput::make('line')
                                ->label('Строка')
                                ->required()
                                ->maxLength(1000),
                        )
                        ->defaultItems(1)
                        ->minItems(1)
                        ->collapsible()
                        ->collapsed(),
                ])
                ->defaultItems(1)
                ->collapsible()
                ->collapsed()
                ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                ->reorderable(),
        ];
    }
}
