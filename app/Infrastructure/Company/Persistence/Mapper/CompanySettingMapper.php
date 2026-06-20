<?php

namespace App\Infrastructure\Company\Persistence\Mapper;

use App\Domain\Company\Entity\CompanySetting;
use App\Infrastructure\Company\Persistence\Eloquent\CompanySettingModel;

final class CompanySettingMapper
{
    public function toDomain(CompanySettingModel $model): CompanySetting
    {
        return new CompanySetting(
            id: $model->id,
            key: $model->key,
            value: $model->value ?? [],
        );
    }

    public function fillModel(CompanySetting $setting, CompanySettingModel $model): void
    {
        $model->fill([
            'key' => $setting->key(),
            'value' => $setting->value(),
        ]);
    }
}
