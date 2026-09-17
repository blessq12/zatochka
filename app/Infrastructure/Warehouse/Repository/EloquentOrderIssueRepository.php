<?php

namespace App\Infrastructure\Warehouse\Repository;

use App\Domain\Warehouse\Aggregate\OrderIssue;
use App\Domain\Warehouse\Entity\IssueLine;
use App\Domain\Warehouse\Repository\OrderIssueRepository;
use App\Infrastructure\Warehouse\Eloquent\OrderIssueLineModel;
use App\Infrastructure\Warehouse\Eloquent\OrderIssueModel;
use Illuminate\Support\Facades\DB;

final class EloquentOrderIssueRepository implements OrderIssueRepository
{
    public function save(OrderIssue $issue): OrderIssue
    {
        return DB::transaction(function () use ($issue): OrderIssue {
            /** @var OrderIssueModel $model */
            $model = $issue->id() === null
                ? new OrderIssueModel()
                : OrderIssueModel::query()->findOrFail($issue->id());

            $model->order_id = $issue->orderId();
            $model->save();

            if ($issue->id() === null) {
                $issue->assignId((int) $model->id);
            }

            OrderIssueLineModel::query()->where('issue_id', $model->id)->delete();

            foreach ($issue->lines() as $line) {
                $lineModel = new OrderIssueLineModel([
                    'issue_id' => $model->id,
                    'stock_item_id' => $line->stockItemId(),
                    'qty' => $line->qty(),
                ]);
                $lineModel->save();
                $line->assignId((int) $lineModel->id);
            }

            return $issue;
        });
    }

    public function findByOrderId(int $orderId): ?OrderIssue
    {
        /** @var OrderIssueModel|null $model */
        $model = OrderIssueModel::query()
            ->with('lines')
            ->where('order_id', $orderId)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    private function toDomain(OrderIssueModel $model): OrderIssue
    {
        $lines = $model->lines
            ->map(static fn (OrderIssueLineModel $line): IssueLine => new IssueLine(
                (int) $line->id,
                (int) $line->stock_item_id,
                (string) $line->qty,
            ))
            ->values()
            ->all();

        return new OrderIssue(
            (int) $model->id,
            (int) $model->order_id,
            $lines,
        );
    }
}
