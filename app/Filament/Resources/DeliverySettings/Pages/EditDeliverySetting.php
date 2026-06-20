<?php

namespace App\Filament\Resources\DeliverySettings\Pages;

use App\Filament\Resources\DeliverySettings\DeliverySettingResource;
use App\Filament\Resources\SiteSettings\Pages\EditSiteSettingRecord;
use App\Filament\Support\SiteSettingFormData;

class EditDeliverySetting extends EditSiteSettingRecord
{
    protected static string $resource = DeliverySettingResource::class;

    public function getTitle(): string
    {
        return 'Доставка';
    }

    public static function settingKey(): string
    {
        return 'delivery_info';
    }

    protected function valueToFormData(array $value): array
    {
        return SiteSettingFormData::deliveryToForm($value);
    }

    protected function formDataToValue(array $data): array
    {
        return SiteSettingFormData::deliveryFromForm($data);
    }
}
