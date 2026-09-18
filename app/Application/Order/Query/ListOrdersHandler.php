<?php

namespace App\Application\Order\Query;

use App\Application\Order\Assembler\OrderResponseAssembler;
use App\Application\Order\DTO\OrderResponse;
use App\Domain\Order\Repository\OrderRepository;

final readonly class ListOrdersHandler
{
    public function __construct(
        private OrderRepository $orders,
        private OrderResponseAssembler $assembler,
    ) {}

    /**
     * @param  list<string>|null  $statusesIn
     * @param  list<string>|null  $statusesNotIn
     * @return list<OrderResponse>
     */
    public function handle(
        ?int $clientId = null,
        ?string $status = null,
        ?int $masterId = null,
        ?int $equipmentId = null,
        ?array $statusesIn = null,
        ?array $statusesNotIn = null,
    ): array {
        return array_map(
            fn ($order): OrderResponse => $this->assembler->assemble($order),
            $this->orders->all($clientId, $status, $masterId, $equipmentId, $statusesIn, $statusesNotIn),
        );
    }
}
