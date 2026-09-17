<?php

namespace App\Infrastructure\Finance\Repository;

use App\Domain\Finance\Aggregate\OrderPricing;
use App\Domain\Finance\Entity\MaterialLine;
use App\Domain\Finance\Entity\PricingLine;
use App\Domain\Finance\OrderPricingStatus;
use App\Domain\Finance\Repository\OrderPricingRepository;
use App\Infrastructure\Finance\Eloquent\OrderPricingLineModel;
use App\Infrastructure\Finance\Eloquent\OrderPricingMaterialLineModel;
use App\Infrastructure\Finance\Eloquent\OrderPricingModel;
use Illuminate\Support\Facades\DB;

final class EloquentOrderPricingRepository implements OrderPricingRepository
{
    public function save(OrderPricing $pricing): OrderPricing
    {
        return DB::transaction(function () use ($pricing): OrderPricing {
            /** @var OrderPricingModel $model */
            $model = $pricing->id() === null
                ? new OrderPricingModel()
                : OrderPricingModel::query()->findOrFail($pricing->id());

            $model->order_id = $pricing->orderId();
            $model->status = $pricing->status()->value;
            $model->save();

            if ($pricing->id() === null) {
                $pricing->assignId((int) $model->id);
            }

            OrderPricingLineModel::query()->where('pricing_id', $model->id)->delete();
            OrderPricingMaterialLineModel::query()->where('pricing_id', $model->id)->delete();

            foreach ($pricing->lines() as $line) {
                $lineModel = new OrderPricingLineModel([
                    'pricing_id' => $model->id,
                    'work_entry_id' => $line->workEntryId(),
                    'amount' => $line->amount(),
                ]);
                $lineModel->save();
                $line->assignId((int) $lineModel->id);
            }

            foreach ($pricing->materialLines() as $line) {
                $lineModel = new OrderPricingMaterialLineModel([
                    'pricing_id' => $model->id,
                    'stock_item_id' => $line->stockItemId(),
                    'amount' => $line->amount(),
                ]);
                $lineModel->save();
                $line->assignId((int) $lineModel->id);
            }

            return $pricing;
        });
    }

    public function findByOrderId(int $orderId): ?OrderPricing
    {
        /** @var OrderPricingModel|null $model */
        $model = OrderPricingModel::query()
            ->with(['lines', 'materialLines'])
            ->where('order_id', $orderId)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    private function toDomain(OrderPricingModel $model): OrderPricing
    {
        $lines = $model->lines
            ->map(static fn (OrderPricingLineModel $line): PricingLine => new PricingLine(
                (int) $line->id,
                (int) $line->work_entry_id,
                (string) $line->amount,
            ))
            ->values()
            ->all();

        $materialLines = $model->materialLines
            ->map(static fn (OrderPricingMaterialLineModel $line): MaterialLine => new MaterialLine(
                (int) $line->id,
                (int) $line->stock_item_id,
                (string) $line->amount,
            ))
            ->values()
            ->all();

        return new OrderPricing(
            (int) $model->id,
            (int) $model->order_id,
            OrderPricingStatus::from((string) $model->status),
            $lines,
            $materialLines,
        );
    }
}
