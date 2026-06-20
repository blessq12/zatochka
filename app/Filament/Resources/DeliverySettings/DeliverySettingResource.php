<?php

namespace App\Filament\Resources\DeliverySettings;

use App\Filament\Resources\DeliverySettings\Pages\EditDeliverySetting;
use App\Filament\SiteSettings\Schemas\DeliverySettingForm;
use App\Filament\Support\AbstractSiteSettingResource;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class DeliverySettingResource extends AbstractSiteSettingResource
{
    protected static ?string $navigationLabel = 'Доставка';

    protected static ?string $slug = 'delivery';

    protected static ?int $navigationSort = 1;

    protected static ?string $pluralModelLabel = 'Доставка';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    public static function settingKey(): string
    {
        return 'delivery_info';
    }

    public static function form(Schema $schema): Schema
    {
        return DeliverySettingForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => EditDeliverySetting::route('/'),
        ];
    }
}
