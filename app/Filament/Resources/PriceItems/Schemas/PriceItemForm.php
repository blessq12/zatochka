<?php

namespace App\Filament\Resources\PriceItems\Schemas;

use App\Infrastructure\Catalog\Persistence\Eloquent\PriceBlockModel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PriceItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('price_block_id')
                    ->label('Блок прайса')
                    ->options(fn (): array => PriceBlockModel::query()
                        ->orderBy('sort_order')
                        ->get()
                        ->mapWithKeys(fn (PriceBlockModel $block): array => [
                            $block->id => $block->title,
                        ])
                        ->all())
                    ->required()
                    ->searchable(),
                TextInput::make('name')
                    ->label('Наименование')
                    ->required()
                    ->maxLength(255),
                TextInput::make('price')
                    ->label('Цена')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
                TextInput::make('sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                Textarea::make('description')
                    ->label('Описание')
                    ->rows(2)
                    ->maxLength(500),
            ]);
    }
}
