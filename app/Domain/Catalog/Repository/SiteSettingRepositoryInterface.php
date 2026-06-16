<?php

namespace App\Domain\Catalog\Repository;

use App\Domain\Catalog\Entity\SiteSetting;

interface SiteSettingRepositoryInterface
{
    public function findByKey(string $key): ?SiteSetting;

    public function save(SiteSetting $setting): SiteSetting;
}
