<?php

namespace App\Domain\Company\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class CompanyActivated extends ShouldBeStored
{
    public function __construct(
        public int $companyId,
        public int $activatedBy
    ) {}
}
