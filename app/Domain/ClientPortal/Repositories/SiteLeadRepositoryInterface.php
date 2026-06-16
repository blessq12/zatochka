<?php

namespace App\Domain\ClientPortal\Repositories;

use App\Domain\ClientPortal\Entities\SiteLead;

interface SiteLeadRepositoryInterface
{
    public function findById(int $id): ?SiteLead;

    public function save(SiteLead $lead): SiteLead;
}
