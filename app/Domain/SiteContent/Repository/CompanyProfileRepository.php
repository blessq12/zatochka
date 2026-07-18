<?php

namespace App\Domain\SiteContent\Repository;

use App\Domain\SiteContent\Entity\CompanyProfile;

interface CompanyProfileRepository
{
    public function get(): CompanyProfile;

    public function save(CompanyProfile $profile): void;
}
