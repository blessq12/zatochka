<?php

namespace App\Application\Finance\Query;

use App\Application\Finance\Assembler\CashEntryResponseAssembler;
use App\Domain\Finance\CashEntryType;
use App\Domain\Finance\Repository\CashEntryRepository;
use DateTimeImmutable;

final readonly class ListCashEntriesHandler
{
    public function __construct(
        private CashEntryRepository $entries,
        private CashEntryResponseAssembler $assembler,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, summary: array{income: string, expense: string, net: string, balance: string}}
     */
    public function handle(
        ?string $from = null,
        ?string $to = null,
        ?string $type = null,
    ): array {
        $fromAt = $from ? new DateTimeImmutable($from.' 00:00:00') : null;
        $toAt = $to ? new DateTimeImmutable($to.' 23:59:59') : null;
        $typeEnum = $type ? CashEntryType::from($type) : null;

        $items = [];
        foreach ($this->entries->list($fromAt, $toAt, $typeEnum) as $entry) {
            $items[] = $this->assembler->assemble($entry)->toArray();
        }

        return [
            'items' => $items,
            'summary' => $this->entries->summarize($fromAt, $toAt),
        ];
    }
}
