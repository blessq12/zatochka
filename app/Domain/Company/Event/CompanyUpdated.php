<?php

namespace App\Domain\Company\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class CompanyUpdated extends ShouldBeStored
{
    public function __construct(
        public int $companyId,
        public string $name,
        public string $legalName,
        public ?string $description,
        public ?string $website,
        public ?string $phone,
        public ?string $email,
        public int $updatedBy
    ) {}
}
