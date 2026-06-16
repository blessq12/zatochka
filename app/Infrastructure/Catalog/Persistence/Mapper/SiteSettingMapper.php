<?php

namespace App\Infrastructure\Catalog\Persistence\Mapper;

use App\Domain\Catalog\Entity\SiteSetting;
use App\Infrastructure\Catalog\Persistence\Eloquent\SiteSettingModel;

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
