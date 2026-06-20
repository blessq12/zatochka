<?php

namespace App\Infrastructure\Company\Persistence\Repository;

use App\Domain\Company\Entity\CompanySetting;
use App\Domain\Company\Repository\CompanySettingRepositoryInterface;
use App\Infrastructure\Company\Persistence\Eloquent\CompanySettingModel;
use App\Infrastructure\Company\Persistence\Mapper\CompanySettingMapper;

final class EloquentCompanySettingRepository implements CompanySettingRepositoryInterface
{
    private const KEYS = ['contacts', 'schedule', 'company'];

    public function __construct(
        private CompanySettingMapper $mapper,
    ) {}

    public function findByKey(string $key): ?CompanySetting
    {
        if (! in_array($key, self::KEYS, true)) {
            return null;
        }

        $model = CompanySettingModel::query()->where('key', $key)->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(CompanySetting $setting): CompanySetting
    {
        $model = CompanySettingModel::query()->firstOrNew(['key' => $setting->key()]);
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

        return CompanySettingModel::query()
            ->whereIn('key', $allowedKeys)
            ->get()
            ->mapWithKeys(fn (CompanySettingModel $model) => [
                $model->key => $model->value ?? [],
            ])
            ->all();
    }
}
