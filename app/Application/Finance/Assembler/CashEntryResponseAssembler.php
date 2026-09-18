<?php

namespace App\Application\Finance\Assembler;

use App\Application\Finance\DTO\CashEntryResponse;
use App\Domain\Finance\Aggregate\CashEntry;

final readonly class CashEntryResponseAssembler
{
    public function assemble(CashEntry $entry): CashEntryResponse
    {
        return new CashEntryResponse(
            (int) $entry->id(),
            $entry->type()->value,
            $entry->amount(),
            $entry->occurredAt()->format('Y-m-d\TH:i:sP'),
            $entry->source()->value,
            $entry->orderId(),
            $entry->comment(),
        );
    }
}
