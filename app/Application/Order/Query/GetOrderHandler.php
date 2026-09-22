<?php

namespace App\Application\Order\Query;

use App\Application\Order\Assembler\OrderResponseAssembler;
use App\Application\Order\DTO\OrderResponse;
use App\Domain\Order\Repository\OrderRepository;
use App\Shared\Domain\ForbiddenException;

final readonly class GetOrderHandler
{
    public function __construct(
        private OrderRepository $orders,
        private OrderResponseAssembler $assembler,
    ) {}

    public function handle(
        int $id,
        ?int $asClientId = null,
        ?int $asMasterId = null,
        bool $includeComments = false,
    ): ?OrderResponse {
        $order = $this->orders->findById($id);
        if ($order === null) {
            return null;
        }

        if ($asClientId !== null && $order->clientId() !== $asClientId) {
            throw new ForbiddenException('Forbidden.');
        }

        if ($asMasterId !== null && $order->masterId() !== $asMasterId) {
            throw new ForbiddenException('Forbidden.');
        }

        return $this->assembler->assemble($order, $includeComments);
    }
}
