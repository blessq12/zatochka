<?php

namespace App\Domain\Company\Events;

use App\Domain\Shared\Events\DomainEvent;

class BranchActivated extends DomainEvent
{
    public function __construct(
        private readonly int $branchId
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'BranchActivated';
    }

    public function eventData(): array
    {
        return [
            'branch_id' => $this->branchId,
        ];
    }

    public function branchId(): int
    {
        return $this->branchId;
    }
}
