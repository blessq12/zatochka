<?php

namespace App\Domain\Company\Repository;

use App\Domain\Company\Entity\SiteContent;

interface SiteContentRepositoryInterface
{
    public function findByKey(string $key): ?SiteContent;

    /**
     * @param  list<string>  $keys
     * @return array<string, array<string, mixed>>
     */
    public function getValuesByKeys(array $keys): array;
}
