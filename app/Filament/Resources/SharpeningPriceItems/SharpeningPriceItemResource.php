<?php

namespace App\Filament\Resources\SharpeningPriceItems;

use App\Domain\Pricing\Enum\PriceType;
use App\Filament\Resources\SharpeningPriceItems\Pages\CreateSharpeningPriceItem;
use App\Filament\Resources\SharpeningPriceItems\Pages\EditSharpeningPriceItem;
use App\Filament\Resources\SharpeningPriceItems\Pages\ListSharpeningPriceItems;
use App\Filament\Support\AbstractPriceItemResource;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class SharpeningPriceItemResource extends AbstractPriceItemResource
{
    protected static ?string $navigationLabel = 'Заточка';

    protected static ?string $slug = 'sharpening';

    protected static ?int $navigationSort = 1;

    protected static ?string $pluralModelLabel = 'Заточка';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScissors;

    public static function priceType(): PriceType
    {
        return PriceType::Sharpening;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSharpeningPriceItems::route('/'),
            'create' => CreateSharpeningPriceItem::route('/create'),
            'edit' => EditSharpeningPriceItem::route('/{record}/edit'),
        ];
    }
}
