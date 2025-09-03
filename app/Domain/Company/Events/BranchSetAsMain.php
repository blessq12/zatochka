<?php

namespace App\Domain\Company\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Company\ValueObjects\BranchId;
use App\Domain\Company\ValueObjects\CompanyId;

class BranchSetAsMain extends DomainEvent
{
    public function __construct(
        private readonly BranchId $branchId,
        private readonly CompanyId $companyId
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
            'branch_id' => (string) $this->branchId,
            'company_id' => (string) $this->companyId,
        ];
    }

    public function branchId(): BranchId
    {
        return $this->branchId;
    }

    public function companyId(): CompanyId
    {
        return $this->companyId;
    }
}
