<?php

namespace App\Application\Warehouse\Command;

use App\Application\Warehouse\Assembler\WarehouseResponseAssembler;
use App\Application\Warehouse\DTO\OrderIssueResponse;
use App\Domain\Warehouse\Aggregate\OrderIssue;
use App\Domain\Warehouse\Repository\OrderIssueRepository;
use App\Domain\Warehouse\Repository\StockItemRepository;
use App\Shared\Domain\DomainException;
use Illuminate\Support\Facades\DB;

final readonly class ReplaceOrderIssueHandler
{
    public function __construct(
        private OrderIssueRepository $issues,
        private StockItemRepository $items,
        private WarehouseResponseAssembler $assembler,
    ) {}

    /**
     * @param  list<array{stock_item_id: int, qty: string|int|float}>  $lines
     */
    public function handle(int $orderId, array $lines): OrderIssueResponse
    {
        return DB::transaction(function () use ($orderId, $lines): OrderIssueResponse {
            $issue = $this->issues->findByOrderId($orderId)
                ?? OrderIssue::forOrder($orderId);

            $normalized = [];
            foreach ($lines as $row) {
                $normalized[] = [
                    'stock_item_id' => (int) $row['stock_item_id'],
                    'qty' => (string) $row['qty'],
                ];
            }

            $oldByItem = [];
            foreach ($issue->lines() as $line) {
                $oldByItem[$line->stockItemId()] = $line->qty();
            }

            $newByItem = [];
            foreach ($normalized as $row) {
                $newByItem[$row['stock_item_id']] = $row['qty'];
            }

            $ids = array_values(array_unique(array_merge(
                array_keys($oldByItem),
                array_keys($newByItem),
            )));

            $stockItems = [];
            foreach ($this->items->findByIds($ids) as $item) {
                $stockItems[(int) $item->id()] = $item;
            }

            foreach ($ids as $id) {
                if (! isset($stockItems[$id])) {
                    throw new DomainException('Stock item not found.');
                }
            }

            foreach ($oldByItem as $stockItemId => $qty) {
                $stockItems[$stockItemId]->receive((string) $qty);
            }

            foreach ($newByItem as $stockItemId => $qty) {
                $stockItems[$stockItemId]->issue((string) $qty);
            }

            foreach ($stockItems as $item) {
                $this->items->save($item);
            }

            $issue->replaceLines($normalized);

            return $this->assembler->orderIssue($this->issues->save($issue));
        });
    }
}
