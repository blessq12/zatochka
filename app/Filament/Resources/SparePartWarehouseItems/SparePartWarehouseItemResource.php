<?php

namespace App\Filament\Resources\SparePartWarehouseItems;

use App\Domain\Warehouse\Enum\WarehouseItemType;
use App\Filament\Resources\SparePartWarehouseItems\Pages\CreateSparePartWarehouseItem;
use App\Filament\Resources\SparePartWarehouseItems\Pages\EditSparePartWarehouseItem;
use App\Filament\Resources\SparePartWarehouseItems\Pages\ListSparePartWarehouseItems;
use App\Filament\Support\AbstractWarehouseItemResource;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class SparePartWarehouseItemResource extends AbstractWarehouseItemResource
{
    protected static ?string $navigationLabel = 'Запчасти';

    protected static ?string $slug = 'spare-parts';

    protected static ?int $navigationSort = 2;

    protected static ?string $pluralModelLabel = 'Запчасти';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public static function warehouseItemType(): WarehouseItemType
    {
        return WarehouseItemType::SparePart;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSparePartWarehouseItems::route('/'),
            'create' => CreateSparePartWarehouseItem::route('/create'),
            'edit' => EditSparePartWarehouseItem::route('/{record}/edit'),
        ];
    }
}
