<?php

namespace App\Infrastructure\Finance\Mapper;

use App\Application\Finance\DTO\WorkPriceDTO;
use App\Domain\Finance\Entity\WorkPrice;
use App\Infrastructure\Finance\Model\WorkPriceModel;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final class WorkPriceMapper
{
    public function toDomain(WorkPriceModel $model): WorkPrice
    {
        return WorkPrice::reconstitute(
            new EntityId((int) $model->id),
            new EntityId((int) $model->performed_work_id),
            new EntityId((int) $model->order_item_id),
            new Money((string) $model->base_amount, (string) $model->currency),
            (bool) $model->calculated,
            $model->final_amount !== null
                ? new Money((string) $model->final_amount, (string) $model->currency)
                : null,
        );
    }

    public function toDTO(WorkPriceModel $model): WorkPriceDTO
    {
        return new WorkPriceDTO(
            (int) $model->id,
            (int) $model->performed_work_id,
            (int) $model->order_item_id,
            (string) $model->base_amount,
            (string) $model->currency,
            (bool) $model->calculated,
            $model->final_amount !== null ? (string) $model->final_amount : null,
        );
    }

    public function toPersistence(WorkPrice $workPrice): WorkPriceModel
    {
        $model = new WorkPriceModel();
        $model->id = $workPrice->id()->value;
        $model->performed_work_id = $workPrice->performedWorkId()->value;
        $model->order_item_id = $workPrice->orderItemId()->value;
        $model->base_amount = $workPrice->baseAmount()->amount;
        $model->currency = $workPrice->baseAmount()->currency;
        $model->final_amount = $workPrice->finalAmount()?->amount;
        $model->calculated = $workPrice->isCalculated();

        return $model;
    }
}
