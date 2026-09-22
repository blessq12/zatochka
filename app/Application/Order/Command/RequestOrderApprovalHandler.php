<?php

namespace App\Application\Order\Command;

use App\Application\Order\Assembler\OrderResponseAssembler;
use App\Application\Order\DTO\OrderResponse;
use App\Domain\Order\Aggregate\OrderComment;
use App\Domain\Order\OrderCommentKind;
use App\Domain\Order\Repository\OrderCommentRepository;
use App\Domain\Order\Repository\OrderRepository;
use App\Shared\Domain\DomainException;
use App\Shared\Domain\ForbiddenException;
use Illuminate\Support\Facades\DB;

final readonly class RequestOrderApprovalHandler
{
    public function __construct(
        private OrderRepository $orders,
        private OrderCommentRepository $comments,
        private OrderResponseAssembler $assembler,
    ) {}

    public function handle(int $orderId, int $masterId, string $body): OrderResponse
    {
        return DB::transaction(function () use ($orderId, $masterId, $body): OrderResponse {
            $order = $this->orders->findById($orderId);
            if ($order === null) {
                throw new DomainException('Order not found.');
            }

            if ($order->masterId() !== $masterId) {
                throw new ForbiddenException('Forbidden.');
            }

            $order->requestApproval($masterId);
            $saved = $this->orders->save($order);

            $this->comments->save(OrderComment::create(
                $orderId,
                'masters',
                $masterId,
                $body,
                OrderCommentKind::ApprovalRequest,
            ));

            return $this->assembler->assemble($saved, true);
        });
    }
}
