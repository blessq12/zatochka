<?php

namespace App\Filament\Support;

use App\Domain\Warehouse\Enum\WarehouseItemType;
use App\Filament\Clusters\WarehouseCluster;
use App\Filament\Resources\WarehouseItems\Schemas\WarehouseItemForm;
use App\Filament\Resources\WarehouseItems\Tables\WarehouseItemsTable;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractWarehouseItemResource extends Resource
{
    protected static ?string $cluster = WarehouseCluster::class;

    protected static ?string $model = WarehouseItemModel::class;

    protected static ?string $modelLabel = 'позиция';

    abstract public static function warehouseItemType(): WarehouseItemType;

    public static function form(Schema $schema): Schema
    {
        return WarehouseItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WarehouseItemsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return WarehouseItemScope::byType(
            parent::getEloquentQuery(),
            static::warehouseItemType(),
        );
    }
}
