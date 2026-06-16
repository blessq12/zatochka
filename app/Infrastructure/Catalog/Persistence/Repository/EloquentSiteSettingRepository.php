<?php

namespace App\Infrastructure\Catalog\Persistence\Repository;

use App\Domain\Catalog\Entity\SiteSetting;
use App\Domain\Catalog\Repository\SiteSettingRepositoryInterface;
use App\Infrastructure\Catalog\Persistence\Eloquent\SiteSettingModel;
use App\Infrastructure\Catalog\Persistence\Mapper\SiteSettingMapper;

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

    public function getValuesByKeys(array $keys): array
    {
        return SiteSettingModel::query()
            ->whereIn('key', $keys)
            ->get()
            ->mapWithKeys(fn (SiteSettingModel $model) => [
                $model->key => $model->value ?? [],
            ])
            ->all();
    }
}
