<?php

namespace App\Domain\Company\Repository;

use App\Domain\Company\Entity\CompanySetting;

interface CompanySettingRepositoryInterface
{
    public function findByKey(string $key): ?CompanySetting;

    public function save(CompanySetting $setting): CompanySetting;

    /**
     * @param  list<string>  $keys
     * @return array<string, array<string, mixed>>
     */
    public function getValuesByKeys(array $keys): array;
}
