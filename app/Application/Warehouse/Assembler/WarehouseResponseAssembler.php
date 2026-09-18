<?php

namespace App\Application\Warehouse\Assembler;

use App\Application\Warehouse\DTO\OrderIssueResponse;
use App\Application\Warehouse\DTO\StockItemResponse;
use App\Domain\Warehouse\Aggregate\OrderIssue;
use App\Domain\Warehouse\Aggregate\StockItem;

final readonly class WarehouseResponseAssembler
{
    public function stockItem(StockItem $item): StockItemResponse
    {
        return new StockItemResponse(
            (int) $item->id(),
            $item->category()->value,
            $item->name(),
            $item->qtyOnHand(),
            $item->unit(),
        );
    }

    public function orderIssue(OrderIssue $issue): OrderIssueResponse
    {
        $lines = [];
        foreach ($issue->lines() as $line) {
            $lines[] = [
                'id' => $line->id(),
                'stock_item_id' => $line->stockItemId(),
                'qty' => $line->qty(),
            ];
        }

        return new OrderIssueResponse(
            (int) $issue->id(),
            $issue->orderId(),
            $lines,
        );
    }
}
