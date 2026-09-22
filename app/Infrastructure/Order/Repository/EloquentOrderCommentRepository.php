<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Order\Aggregate\OrderComment;
use App\Domain\Order\OrderCommentKind;
use App\Domain\Order\Repository\OrderCommentRepository;
use App\Infrastructure\Order\Eloquent\OrderCommentModel;
use DateTimeImmutable;

final class EloquentOrderCommentRepository implements OrderCommentRepository
{
    public function save(OrderComment $comment): OrderComment
    {
        $model = new OrderCommentModel([
            'order_id' => $comment->orderId(),
            'author_type' => $comment->authorType(),
            'author_id' => $comment->authorId(),
            'body' => $comment->body(),
            'kind' => $comment->kind()->value,
        ]);
        $model->save();
        $comment->assignId((int) $model->id);
        $comment->syncCreatedAt($this->toImmutable($model->created_at));

        return $comment;
    }

    public function listByOrderId(int $orderId): array
    {
        $models = OrderCommentModel::query()
            ->where('order_id', $orderId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $items = [];
        foreach ($models as $model) {
            $items[] = $this->toDomain($model);
        }

        return $items;
    }

    private function toDomain(OrderCommentModel $model): OrderComment
    {
        return new OrderComment(
            (int) $model->id,
            (int) $model->order_id,
            (string) $model->author_type,
            (int) $model->author_id,
            (string) $model->body,
            OrderCommentKind::tryFrom((string) ($model->kind ?? 'regular'))
                ?? OrderCommentKind::Regular,
            $this->toImmutable($model->created_at),
        );
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
