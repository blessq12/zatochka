<?php

namespace App\Domain\Company\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Company\ValueObjects\BranchId;

class BranchDeactivated extends DomainEvent
{
    public function __construct(
        private readonly BranchId $branchId
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'BranchDeactivated';
    }

    public function eventData(): array
    {
        return [
            'branch_id' => (string) $this->branchId,
        ];
    }

    public function branchId(): BranchId
    {
        return $this->branchId;
    }
}
