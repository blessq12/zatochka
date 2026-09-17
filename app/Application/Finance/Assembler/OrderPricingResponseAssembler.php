<?php

namespace App\Application\Finance\Assembler;

use App\Application\Finance\DTO\OrderPricingResponse;
use App\Domain\Finance\Aggregate\OrderPricing;

final readonly class OrderPricingResponseAssembler
{
    public function assemble(OrderPricing $pricing): OrderPricingResponse
    {
        $lines = [];
        foreach ($pricing->lines() as $line) {
            $lines[] = [
                'id' => $line->id(),
                'work_entry_id' => $line->workEntryId(),
                'amount' => $line->amount(),
            ];
        }

        $materialLines = [];
        foreach ($pricing->materialLines() as $line) {
            $materialLines[] = [
                'id' => $line->id(),
                'stock_item_id' => $line->stockItemId(),
                'amount' => $line->amount(),
            ];
        }

        return new OrderPricingResponse(
            (int) $pricing->id(),
            $pricing->orderId(),
            $pricing->status()->value,
            $pricing->total(),
            $lines,
            $materialLines,
        );
    }
}
