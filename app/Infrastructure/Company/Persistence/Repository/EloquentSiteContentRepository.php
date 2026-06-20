<?php

namespace App\Infrastructure\Company\Persistence\Repository;

use App\Domain\Company\Entity\SiteContent;
use App\Domain\Company\Repository\SiteContentRepositoryInterface;
use App\Infrastructure\Company\Persistence\Eloquent\SiteContentModel;
use App\Infrastructure\Company\Persistence\Mapper\SiteContentMapper;

final class EloquentSiteContentRepository implements SiteContentRepositoryInterface
{
    public const KEYS = [
        'contacts',
        'schedule',
        'company',
        'delivery_info',
        'faq',
    ];

    public function __construct(
        private SiteContentMapper $mapper,
    ) {}

    public function findByKey(string $key): ?SiteContent
    {
        if (! in_array($key, self::KEYS, true)) {
            return null;
        }

        $model = SiteContentModel::query()->where('key', $key)->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function getValuesByKeys(array $keys): array
    {
        $allowedKeys = array_values(array_intersect($keys, self::KEYS));

        if ($allowedKeys === []) {
            return [];
        }

        return SiteContentModel::query()
            ->whereIn('key', $allowedKeys)
            ->get()
            ->mapWithKeys(fn (SiteContentModel $model) => [
                $model->key => $model->value ?? [],
            ])
            ->all();
    }
}
