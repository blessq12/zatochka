<?php

namespace App\Application\OrderFulfillment\Command;

use App\Domain\OrderFulfillment\Entity\OrderTool;
use App\Domain\OrderFulfillment\Enum\OrderUrgency;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;

final readonly class CreateOrderCommand
{
    /**
     * @param  list<string>  $serviceTypes
     * @param  list<OrderTool>  $tools
     */
    public function __construct(
        public array $serviceTypes,
        public ?int $clientId = null,
        public ?ClientSnapshot $clientSnapshot = null,
        public ?int $leadId = null,
        public ?OrderUrgency $urgency = null,
        public bool $isWarranty = false,
        public bool $needsDelivery = false,
        public ?string $deliveryAddress = null,
        public ?string $problemDescription = null,
        public ?int $equipmentId = null,
        public ?int $branchId = null,
        public ?int $warrantyParentOrderId = null,
        public ?int $masterId = null,
        public ?int $managerId = null,
        public array $tools = [],
    ) {}
}
