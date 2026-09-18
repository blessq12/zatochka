<?php

namespace App\Domain\Warehouse\Repository;

use App\Domain\Warehouse\Aggregate\OrderIssue;

interface OrderIssueRepository
{
    public function save(OrderIssue $issue): OrderIssue;

    public function findByOrderId(int $orderId): ?OrderIssue;
}
