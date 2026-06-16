<?php

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Entities\SiteSetting;

interface SiteSettingRepositoryInterface
{
    public function findByKey(string $key): ?SiteSetting;

    public function save(SiteSetting $setting): SiteSetting;
}
