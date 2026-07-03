<?php

namespace App\Filament\Resources\PriceItems\Schemas;

use App\Domain\Pricing\Enum\PricePrefix;
use App\Domain\Pricing\Enum\PriceType;
use App\Filament\Support\PriceItemScope;
use App\Infrastructure\Pricing\Persistence\Eloquent\PriceBlockModel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PriceItemForm
{
    public static function configure(Schema $schema, PriceType $type): Schema
    {
        return $schema
            ->components([
                Select::make('price_block_id')
                    ->label('Категория')
                    ->options(fn (): array => PriceBlockModel::query()
                        ->where('type', $type)
                        ->orderBy('sort_order')
                        ->get()
                        ->mapWithKeys(fn (PriceBlockModel $block): array => [
                            $block->id => $block->title,
                        ])
                        ->all())
                    ->default(fn (): int => PriceItemScope::blockIdForType($type))
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
                Select::make('price_prefix')
                    ->label('Префикс')
                    ->options(collect(PricePrefix::cases())->mapWithKeys(
                        fn (PricePrefix $prefix): array => [$prefix->value => $prefix->label()],
                    )->all())
                    ->placeholder('—')
                    ->nullable(),
                Textarea::make('description')
                    ->label('Описание')
                    ->rows(2)
                    ->maxLength(500),
            ]);
    }
}
