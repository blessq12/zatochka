<?php

namespace App\Application\Finance\Command;

use App\Application\Finance\Assembler\CashEntryResponseAssembler;
use App\Application\Finance\DTO\CashEntryResponse;
use App\Domain\Finance\Aggregate\CashEntry;
use App\Domain\Finance\CashEntrySource;
use App\Domain\Finance\Repository\CashEntryRepository;
use App\Shared\Domain\DomainException;
use DateTimeImmutable;

final readonly class RecordManualCashEntryHandler
{
    public function __construct(
        private CashEntryRepository $entries,
        private CashEntryResponseAssembler $assembler,
    ) {}

    public function handle(
        string $type,
        string $amount,
        ?string $occurredAt = null,
        ?string $comment = null,
    ): CashEntryResponse {
        $when = $occurredAt !== null && $occurredAt !== ''
            ? new DateTimeImmutable($occurredAt)
            : new DateTimeImmutable('now');

        $entry = match ($type) {
            'income' => CashEntry::income(
                $amount,
                $when,
                CashEntrySource::Manual,
                null,
                $comment,
            ),
            'expense' => CashEntry::expense($amount, $when, $comment),
            default => throw new DomainException('type must be income or expense.'),
        };

        return $this->assembler->assemble($this->entries->save($entry));
    }
}
