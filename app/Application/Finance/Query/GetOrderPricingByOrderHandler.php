<?php

namespace App\Application\Finance\Query;

use App\Application\Finance\Assembler\OrderPricingResponseAssembler;
use App\Application\Finance\DTO\OrderPricingResponse;
use App\Domain\Finance\Repository\OrderPricingRepository;

final readonly class GetOrderPricingByOrderHandler
{
    public function __construct(
        private OrderPricingRepository $pricings,
        private OrderPricingResponseAssembler $assembler,
    ) {}

    public function handle(int $orderId): ?OrderPricingResponse
    {
        $pricing = $this->pricings->findByOrderId($orderId);

        return $pricing === null ? null : $this->assembler->assemble($pricing);
    }
}
