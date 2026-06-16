<?php

namespace App\Infrastructure\Persistence\Mappers\Catalog;

use App\Domain\Catalog\Entities\SiteSetting;
use App\Infrastructure\Persistence\Eloquent\Models\Catalog\SiteSettingModel;

final class SiteSettingMapper
{
    public function toDomain(SiteSettingModel $model): SiteSetting
    {
        return new SiteSetting(
            id: $model->id,
            key: $model->key,
            value: $model->value ?? [],
        );
    }

    public function fillModel(SiteSetting $setting, SiteSettingModel $model): void
    {
        $model->fill([
            'key' => $setting->key(),
            'value' => $setting->value(),
        ]);
    }
}
