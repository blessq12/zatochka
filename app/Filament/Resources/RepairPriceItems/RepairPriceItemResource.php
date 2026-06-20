<?php

namespace App\Filament\Resources\RepairPriceItems;

use App\Domain\Pricing\Enum\PriceType;
use App\Filament\Resources\RepairPriceItems\Pages\CreateRepairPriceItem;
use App\Filament\Resources\RepairPriceItems\Pages\EditRepairPriceItem;
use App\Filament\Resources\RepairPriceItems\Pages\ListRepairPriceItems;
use App\Filament\Support\AbstractPriceItemResource;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class RepairPriceItemResource extends AbstractPriceItemResource
{
    protected static ?string $navigationLabel = 'Ремонт';

    protected static ?string $slug = 'repair';

    protected static ?int $navigationSort = 2;

    protected static ?string $pluralModelLabel = 'Ремонт';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    public static function priceType(): PriceType
    {
        return PriceType::Repair;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRepairPriceItems::route('/'),
            'create' => CreateRepairPriceItem::route('/create'),
            'edit' => EditRepairPriceItem::route('/{record}/edit'),
        ];
    }
}
