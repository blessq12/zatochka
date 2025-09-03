<?php

namespace App\Domain\Company\Events;

use App\Domain\Shared\Events\DomainEvent;

class BranchSetAsMain extends DomainEvent
{
    public function __construct(
        private readonly int $branchId,
        private readonly int $companyId
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'BranchSetAsMain';
    }

    public function eventData(): array
    {
        return [
            'branch_id' => $this->branchId,
            'company_id' => $this->companyId,
        ];
    }

    public function branchId(): int
    {
        return $this->branchId;
    }

    public function companyId(): int
    {
        return $this->companyId;
    }
}
