<?php

namespace App\Filament\Support;

use App\Domain\Pricing\Enum\PriceType;
use App\Filament\Clusters\PricingCluster;
use App\Filament\Resources\PriceItems\Schemas\PriceItemForm;
use App\Filament\Resources\PriceItems\Tables\PriceItemsTable;
use App\Infrastructure\Pricing\Persistence\Eloquent\PriceItemModel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractPriceItemResource extends Resource
{
    protected static ?string $cluster = PricingCluster::class;

    protected static ?string $model = PriceItemModel::class;

    protected static ?string $modelLabel = 'позиция';

    abstract public static function priceType(): PriceType;

    public static function form(Schema $schema): Schema
    {
        return PriceItemForm::configure($schema, static::priceType());
    }

    public static function table(Table $table): Table
    {
        return PriceItemsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return PriceItemScope::byType(
            parent::getEloquentQuery()->with('block'),
            static::priceType(),
        );
    }
}
