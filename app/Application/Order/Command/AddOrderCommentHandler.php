<?php

namespace App\Application\Order\Command;

use App\Application\Order\Assembler\OrderResponseAssembler;
use App\Application\Order\DTO\OrderResponse;
use App\Domain\Order\Aggregate\OrderComment;
use App\Domain\Order\Repository\OrderCommentRepository;
use App\Domain\Order\Repository\OrderRepository;
use App\Shared\Domain\DomainException;
use App\Shared\Domain\ForbiddenException;
use Illuminate\Support\Facades\DB;

final readonly class AddOrderCommentHandler
{
    public function __construct(
        private OrderRepository $orders,
        private OrderCommentRepository $comments,
        private OrderResponseAssembler $assembler,
    ) {}

    public function handle(
        int $orderId,
        string $authorType,
        int $authorId,
        string $body,
    ): OrderResponse {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            throw new DomainException('Order not found.');
        }

        if ($authorType === 'masters' && $order->masterId() !== $authorId) {
            throw new ForbiddenException('Forbidden.');
        }

        if (! in_array($authorType, ['managers', 'masters'], true)) {
            throw new ForbiddenException('Forbidden.');
        }

        $this->comments->save(OrderComment::create(
            $orderId,
            $authorType,
            $authorId,
            $body,
        ));

        return $this->assembler->assemble($order, true);
    }
}
