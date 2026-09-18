<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Order\Aggregate\OrderDraft;
use App\Domain\Order\OrderDraftSource;
use App\Domain\Order\OrderDraftStatus;
use App\Domain\Order\Repository\OrderDraftRepository;
use App\Infrastructure\Order\Eloquent\OrderDraftModel;
use DateTimeImmutable;

final class EloquentOrderDraftRepository implements OrderDraftRepository
{
    public function save(OrderDraft $draft): OrderDraft
    {
        /** @var OrderDraftModel $model */
        $model = $draft->id() === null
            ? new OrderDraftModel()
            : OrderDraftModel::query()->findOrFail($draft->id());

        $model->source = $draft->source()->value;
        $model->status = $draft->status()->value;
        $model->client_id = $draft->clientId();
        $model->full_name = $draft->fullName();
        $model->phone = $draft->phone();
        $model->service_type = $draft->serviceType();
        $model->payload = $draft->payload();
        $model->needs_delivery = $draft->needsDelivery();
        $model->delivery_address = $draft->deliveryAddress();
        $model->comment = $draft->comment();
        $model->order_id = $draft->orderId();
        $model->save();

        if ($draft->id() === null) {
            $draft->assignId((int) $model->id);
        }

        $draft->syncTimestamps(
            $this->toImmutable($model->created_at),
            $this->toImmutable($model->updated_at),
        );

        return $draft;
    }

    public function findById(int $id): ?OrderDraft
    {
        /** @var OrderDraftModel|null $model */
        $model = OrderDraftModel::query()->find($id);

        return $model === null ? null : $this->toDomain($model);
    }

    public function all(
        ?int $clientId = null,
        ?string $status = null,
        ?string $source = null,
        ?string $phone = null,
        ?array $statusesIn = null,
    ): array {
        $query = OrderDraftModel::query()->orderByDesc('id');

        if ($clientId !== null) {
            $query->where('client_id', $clientId);
        }
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }
        if ($statusesIn !== null && $statusesIn !== []) {
            $query->whereIn('status', $statusesIn);
        }
        if ($source !== null && $source !== '') {
            $query->where('source', $source);
        }
        if ($phone !== null && $phone !== '') {
            $query->where('phone', 'like', '%'.$phone.'%');
        }

        return $query->get()
            ->map(fn (OrderDraftModel $model): OrderDraft => $this->toDomain($model))
            ->all();
    }

    private function toDomain(OrderDraftModel $model): OrderDraft
    {
        $draft = new OrderDraft(
            (int) $model->id,
            OrderDraftSource::from((string) $model->source),
            OrderDraftStatus::from((string) $model->status),
            $model->client_id !== null ? (int) $model->client_id : null,
            $model->full_name,
            $model->phone,
            (string) $model->service_type,
            is_array($model->payload) ? $model->payload : [],
            (bool) $model->needs_delivery,
            $model->delivery_address,
            $model->comment,
            $model->order_id !== null ? (int) $model->order_id : null,
        );
        $draft->syncTimestamps(
            $this->toImmutable($model->created_at),
            $this->toImmutable($model->updated_at),
        );

        return $draft;
    }

    private function toImmutable(mixed $value): ?DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        return DateTimeImmutable::createFromInterface($value);
    }
}
