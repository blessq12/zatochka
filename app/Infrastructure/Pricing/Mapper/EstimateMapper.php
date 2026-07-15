<?php

namespace App\Infrastructure\Pricing\Mapper;

use App\Application\Pricing\DTO\EstimateDTO;
use App\Domain\Pricing\Entity\Discount;
use App\Domain\Pricing\Entity\Estimate;
use App\Domain\Pricing\Entity\ItemPrice;
use App\Domain\Pricing\VO\DiscountType;
use App\Infrastructure\Pricing\Model\DiscountModel;
use App\Infrastructure\Pricing\Model\EstimateModel;
use App\Infrastructure\Pricing\Model\ItemPriceModel;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final class EstimateMapper
{
    public function toDomain(EstimateModel $model): Estimate
    {
        $itemPrice = null;

        if ($model->itemPrice !== null) {
            $discount = null;

            if ($model->itemPrice->discount !== null) {
                $discount = new Discount(
                    new EntityId((int) $model->itemPrice->discount->id),
                    DiscountType::from((string) $model->itemPrice->discount->type),
                    (string) $model->itemPrice->discount->value,
                    $model->itemPrice->discount->reason !== null
                        ? (string) $model->itemPrice->discount->reason
                        : null,
                );
            }

            $itemPrice = ItemPrice::reconstitute(
                new EntityId((int) $model->itemPrice->id),
                new EntityId((int) $model->itemPrice->order_item_id),
                new Money((string) $model->itemPrice->base_amount, (string) $model->itemPrice->currency),
                $discount,
                $model->itemPrice->final_amount !== null
                    ? new Money((string) $model->itemPrice->final_amount, (string) $model->itemPrice->currency)
                    : null,
            );
        }

        return Estimate::reconstitute(
            new EntityId((int) $model->id),
            new EntityId((int) $model->order_item_id),
            new Money((string) $model->estimated_amount, (string) $model->currency),
            $itemPrice,
            (bool) $model->calculated,
        );
    }

    public function toPersistence(Estimate $estimate, ?EstimateModel $model = null): EstimateModel
    {
        $model ??= new EstimateModel();
        $model->id = $estimate->id()->value;
        $model->order_item_id = $estimate->orderItemId()->value;
        $model->estimated_amount = $estimate->estimatedAmount()->amount;
        $model->currency = $estimate->estimatedAmount()->currency;
        $model->calculated = $estimate->isCalculated();

        return $model;
    }

    public function itemPriceToPersistence(Estimate $estimate): ?ItemPriceModel
    {
        $price = $estimate->itemPrice();

        if ($price === null) {
            return null;
        }

        $row = new ItemPriceModel();
        $row->id = $price->id()->value;
        $row->estimate_id = $estimate->id()->value;
        $row->order_item_id = $price->orderItemId()->value;
        $row->base_amount = $price->baseAmount()->amount;
        $row->currency = $price->baseAmount()->currency;
        $row->final_amount = $price->finalAmount()?->amount;

        return $row;
    }

    public function discountToPersistence(Estimate $estimate): ?DiscountModel
    {
        $price = $estimate->itemPrice();
        $discount = $price?->discount();

        if ($price === null || $discount === null) {
            return null;
        }

        $row = new DiscountModel();
        $row->id = $discount->id()->value;
        $row->item_price_id = $price->id()->value;
        $row->type = $discount->type()->value;
        $row->value = $discount->value();
        $row->reason = $discount->reason();

        return $row;
    }

    public function toDTO(EstimateModel $model): EstimateDTO
    {
        return new EstimateDTO(
            (int) $model->id,
            (int) $model->order_item_id,
            (string) $model->estimated_amount,
            (string) $model->currency,
            $model->itemPrice !== null ? (int) $model->itemPrice->id : null,
            $model->itemPrice?->final_amount !== null ? (string) $model->itemPrice->final_amount : null,
            (bool) $model->calculated,
        );
    }
}
