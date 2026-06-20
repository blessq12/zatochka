<?php

namespace App\Domain\SiteSettings\Repository;

use App\Domain\SiteSettings\Entity\SiteSetting;

interface SiteSettingRepositoryInterface
{
    public function findByKey(string $key): ?SiteSetting;

    public function save(SiteSetting $setting): SiteSetting;

    /**
     * @param  list<string>  $keys
     * @return array<string, array<string, mixed>>
     */
    public function getValuesByKeys(array $keys): array;
}
