<?php

namespace App\Domain\Company\Events;

use App\Domain\Shared\Events\DomainEvent;

class CompanyActivated extends DomainEvent
{
    public function __construct(
        private readonly int $companyId
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'CompanyActivated';
    }

    public function eventData(): array
    {
        return [
            'company_id' => $this->companyId,
        ];
    }

    public function companyId(): int
    {
        return $this->companyId;
    }
}
