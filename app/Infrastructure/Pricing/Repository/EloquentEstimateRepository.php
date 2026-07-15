<?php

namespace App\Infrastructure\Pricing\Repository;

use App\Domain\Pricing\Entity\Estimate;
use App\Domain\Pricing\Repository\EstimateRepository;
use App\Infrastructure\Pricing\Mapper\EstimateMapper;
use App\Infrastructure\Pricing\Model\DiscountModel;
use App\Infrastructure\Pricing\Model\EstimateModel;
use App\Infrastructure\Pricing\Model\ItemPriceModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use Illuminate\Support\Facades\DB;

final readonly class EloquentEstimateRepository implements EstimateRepository
{
    public function __construct(
        private EstimateMapper $mapper,
    ) {}

    public function save(Estimate $estimate): void
    {
        DB::transaction(function () use ($estimate): void {
            $model = EstimateModel::query()->find($estimate->id()->value);
            $model = $this->mapper->toPersistence($estimate, $model);
            $model->save();

            $itemPriceIds = ItemPriceModel::query()
                ->where('estimate_id', $estimate->id()->value)
                ->pluck('id');

            DiscountModel::query()->whereIn('item_price_id', $itemPriceIds)->delete();
            ItemPriceModel::query()->where('estimate_id', $estimate->id()->value)->delete();

            $this->mapper->itemPriceToPersistence($estimate)?->save();
            $this->mapper->discountToPersistence($estimate)?->save();
        });
    }

    public function findById(EntityId $id): ?Estimate
    {
        $model = EstimateModel::query()->with(['itemPrice.discount'])->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getById(EntityId $id): Estimate
    {
        return $this->findById($id)
            ?? throw new DomainException(sprintf('Estimate %d not found.', $id->value));
    }

    public function findByOrderItemId(EntityId $orderItemId): ?Estimate
    {
        $model = EstimateModel::query()
            ->with(['itemPrice.discount'])
            ->where('order_item_id', $orderItemId->value)
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }
}
