<?php

namespace App\Filament\Resources\PriceItems;

use App\Filament\Clusters\PricingCluster;
use App\Filament\Resources\PriceItems\Pages\CreatePriceItem;
use App\Filament\Resources\PriceItems\Pages\EditPriceItem;
use App\Filament\Resources\PriceItems\Pages\ListPriceItems;
use App\Filament\Resources\PriceItems\Schemas\PriceItemForm;
use App\Filament\Resources\PriceItems\Tables\PriceItemsTable;
use App\Infrastructure\Pricing\Persistence\Eloquent\PriceItemModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PriceItemResource extends Resource
{
    protected static ?string $cluster = PricingCluster::class;

    protected static ?string $model = PriceItemModel::class;

    protected static ?string $navigationLabel = 'Прайс';

    protected static ?string $slug = 'price-items';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'позиция прайса';

    protected static ?string $pluralModelLabel = 'Прайс';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    public static function form(Schema $schema): Schema
    {
        return PriceItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PriceItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPriceItems::route('/'),
            'create' => CreatePriceItem::route('/create'),
            'edit' => EditPriceItem::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('block');
    }
}
