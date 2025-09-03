<?php

namespace App\Domain\Company\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\ValueObjects\BranchId;
use App\Domain\Company\ValueObjects\BranchCode;

class BranchCreated extends DomainEvent
{
    public function __construct(
        private readonly CompanyId $companyId,
        private readonly BranchId $branchId,
        private readonly BranchCode $code
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'BranchCreated';
    }

    public function eventData(): array
    {
        return [
            'company_id' => (string) $this->companyId,
            'branch_id' => (string) $this->branchId,
            'code' => (string) $this->code,
        ];
    }

    public function companyId(): CompanyId
    {
        return $this->companyId;
    }

    public function branchId(): BranchId
    {
        return $this->branchId;
    }

    public function code(): BranchCode
    {
        return $this->code;
    }
}
