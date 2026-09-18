<?php

namespace App\Application\Finance\Command;

use App\Domain\Finance\Repository\CashEntryRepository;
use App\Shared\Domain\DomainException;

final readonly class DeleteManualCashEntryHandler
{
    public function __construct(
        private CashEntryRepository $entries,
    ) {}

    public function handle(int $id): void
    {
        $entry = $this->entries->findById($id);
        if ($entry === null) {
            throw new DomainException('Cash entry not found.');
        }
        $this->entries->delete($entry);
    }
}
