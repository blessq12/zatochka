<?php

namespace App\Infrastructure\Persistence\Repositories\Catalog;

use App\Domain\Catalog\Entities\SiteSetting;
use App\Domain\Catalog\Repositories\SiteSettingRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Catalog\SiteSettingModel;
use App\Infrastructure\Persistence\Mappers\Catalog\SiteSettingMapper;

final class EloquentSiteSettingRepository implements SiteSettingRepositoryInterface
{
    public function __construct(
        private SiteSettingMapper $mapper,
    ) {}

    public function findByKey(string $key): ?SiteSetting
    {
        $model = SiteSettingModel::query()->where('key', $key)->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(SiteSetting $setting): SiteSetting
    {
        $model = SiteSettingModel::query()->firstOrNew(['key' => $setting->key()]);
        $this->mapper->fillModel($setting, $model);
        $model->save();

        return $this->mapper->toDomain($model);
    }
}
