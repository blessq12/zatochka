<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Aggregate\OrderComment;

interface OrderCommentRepository
{
    public function save(OrderComment $comment): OrderComment;

    /**
     * @return list<OrderComment>
     */
    public function listByOrderId(int $orderId): array;
}
