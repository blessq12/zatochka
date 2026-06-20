<?php

namespace App\Filament\Resources\ConsumableWarehouseItems;

use App\Domain\Warehouse\Enum\WarehouseItemType;
use App\Filament\Resources\ConsumableWarehouseItems\Pages\CreateConsumableWarehouseItem;
use App\Filament\Resources\ConsumableWarehouseItems\Pages\EditConsumableWarehouseItem;
use App\Filament\Resources\ConsumableWarehouseItems\Pages\ListConsumableWarehouseItems;
use App\Filament\Support\AbstractWarehouseItemResource;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ConsumableWarehouseItemResource extends AbstractWarehouseItemResource
{
    protected static ?string $navigationLabel = 'Расходники';

    protected static ?string $slug = 'consumables';

    protected static ?int $navigationSort = 1;

    protected static ?string $pluralModelLabel = 'Расходники';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    public static function warehouseItemType(): WarehouseItemType
    {
        return WarehouseItemType::Consumable;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConsumableWarehouseItems::route('/'),
            'create' => CreateConsumableWarehouseItem::route('/create'),
            'edit' => EditConsumableWarehouseItem::route('/{record}/edit'),
        ];
    }
}
