<?php

namespace App\Domain\Company\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class CompanyBankDetailsUpdated extends ShouldBeStored
{
    public function __construct(
        public int $companyId,
        public ?string $bankName,
        public ?string $bankBik,
        public ?string $bankAccount,
        public ?string $bankCorAccount,
        public int $updatedBy
    ) {}
}
