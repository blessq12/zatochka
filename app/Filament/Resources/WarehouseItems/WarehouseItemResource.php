<?php

namespace App\Filament\Resources\WarehouseItems;

use App\Filament\Resources\WarehouseItems\Pages\ListWarehouseItems;
use App\Filament\Resources\WarehouseItems\Tables\WarehouseItemsTable;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WarehouseItemResource extends Resource
{
    protected static ?string $model = WarehouseItemModel::class;

    protected static ?string $navigationLabel = 'Склад';

    protected static ?string $modelLabel = 'позиция';

    protected static ?string $pluralModelLabel = 'Склад';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    public static function table(Table $table): Table
    {
        return WarehouseItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWarehouseItems::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
