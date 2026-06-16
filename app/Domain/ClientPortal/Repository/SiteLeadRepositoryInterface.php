<?php

namespace App\Domain\ClientPortal\Repository;

use App\Domain\ClientPortal\Entity\SiteLead;

interface SiteLeadRepositoryInterface
{
    public function findById(int $id): ?SiteLead;

    public function save(SiteLead $lead): SiteLead;
}
