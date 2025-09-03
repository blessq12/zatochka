<?php

namespace App\Domain\Company\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Company\ValueObjects\CompanyId;

class CompanyDeactivated extends DomainEvent
{
    public function __construct(
        private readonly CompanyId $companyId
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'CompanyDeactivated';
    }

    public function eventData(): array
    {
        return [
            'company_id' => (string) $this->companyId,
        ];
    }

    public function companyId(): CompanyId
    {
        return $this->companyId;
    }
}
