<?php

namespace App\Application\Finance\Command;

use App\Application\Finance\Assembler\OrderPricingResponseAssembler;
use App\Application\Finance\DTO\OrderPricingResponse;
use App\Domain\Finance\Aggregate\OrderPricing;
use App\Domain\Finance\Repository\OrderPricingRepository;

final readonly class UpsertOrderPricingHandler
{
    public function __construct(
        private OrderPricingRepository $pricings,
        private OrderPricingResponseAssembler $assembler,
    ) {}

    /**
     * @param  list<array{work_entry_id: int, amount: string|int|float}>  $lines
     */
    public function handle(int $orderId, array $lines): OrderPricingResponse
    {
        $pricing = $this->pricings->findByOrderId($orderId)
            ?? OrderPricing::forOrder($orderId);

        $normalized = [];
        foreach ($lines as $row) {
            $normalized[] = [
                'work_entry_id' => (int) $row['work_entry_id'],
                'amount' => (string) $row['amount'],
            ];
        }

        $pricing->replaceLines($normalized);

        return $this->assembler->assemble($this->pricings->save($pricing));
    }
}
