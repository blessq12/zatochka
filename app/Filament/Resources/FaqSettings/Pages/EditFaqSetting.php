<?php

namespace App\Filament\Resources\FaqSettings\Pages;

use App\Filament\Resources\FaqSettings\FaqSettingResource;
use App\Filament\Resources\SiteSettings\Pages\EditSiteSettingRecord;
use App\Filament\Support\SiteSettingFormData;

class EditFaqSetting extends EditSiteSettingRecord
{
    protected static string $resource = FaqSettingResource::class;

    public function getTitle(): string
    {
        return 'FAQ';
    }

    public static function settingKey(): string
    {
        return 'faq';
    }

    protected function valueToFormData(array $value): array
    {
        return SiteSettingFormData::faqToForm($value);
    }

    protected function formDataToValue(array $data): array
    {
        return SiteSettingFormData::faqFromForm($data);
    }
}
