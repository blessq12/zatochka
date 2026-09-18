<?php

namespace App\Application\Warehouse\Query;

use App\Application\Warehouse\Assembler\WarehouseResponseAssembler;
use App\Application\Warehouse\DTO\OrderIssueResponse;
use App\Domain\Warehouse\Repository\OrderIssueRepository;

final readonly class GetOrderIssueByOrderHandler
{
    public function __construct(
        private OrderIssueRepository $issues,
        private WarehouseResponseAssembler $assembler,
    ) {}

    public function handle(int $orderId): ?OrderIssueResponse
    {
        $issue = $this->issues->findByOrderId($orderId);

        return $issue === null ? null : $this->assembler->orderIssue($issue);
    }
}
