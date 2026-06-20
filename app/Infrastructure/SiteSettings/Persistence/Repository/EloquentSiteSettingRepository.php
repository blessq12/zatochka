<?php

namespace App\Infrastructure\SiteSettings\Persistence\Repository;

use App\Domain\SiteSettings\Entity\SiteSetting;
use App\Domain\SiteSettings\Repository\SiteSettingRepositoryInterface;
use App\Infrastructure\SiteSettings\Persistence\Eloquent\SiteSettingModel;
use App\Infrastructure\SiteSettings\Persistence\Mapper\SiteSettingMapper;

final class EloquentSiteSettingRepository implements SiteSettingRepositoryInterface
{
    private const KEYS = ['faq', 'delivery_info'];

    public function __construct(
        private SiteSettingMapper $mapper,
    ) {}

    public function findByKey(string $key): ?SiteSetting
    {
        if (! in_array($key, self::KEYS, true)) {
            return null;
        }

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
        $allowedKeys = array_values(array_intersect($keys, self::KEYS));

        if ($allowedKeys === []) {
            return [];
        }

        return SiteSettingModel::query()
            ->whereIn('key', $allowedKeys)
            ->get()
            ->mapWithKeys(fn (SiteSettingModel $model) => [
                $model->key => $model->value ?? [],
            ])
            ->all();
    }
}
