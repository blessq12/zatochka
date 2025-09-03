<?php

namespace App\Domain\Company\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Company\ValueObjects\BranchCode;

class BranchCreated extends DomainEvent
{
    public function __construct(
        private readonly int $companyId,
        private readonly int $branchId,
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
            'company_id' => $this->companyId,
            'branch_id' => $this->branchId,
            'code' => (string) $this->code,
        ];
    }

    public function companyId(): int
    {
        return $this->companyId;
    }

    public function branchId(): int
    {
        return $this->branchId;
    }

    public function code(): BranchCode
    {
        return $this->code;
    }
}
