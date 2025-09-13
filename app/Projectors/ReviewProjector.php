<?php

namespace App\Projectors;

use App\Domain\Review\Event\ReviewCreated;
use App\Domain\Review\Event\ReviewReplyAdded;
use App\Domain\Review\Event\ReviewApproved;
use App\Domain\Review\Entity\Review;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class ReviewProjector extends Projector
{
    public function onReviewCreated(ReviewCreated $event): void
    {
        // Создаем Domain Entity для проекции
        new Review(
            id: (string) $event->reviewId,
            clientId: (string) $event->clientId,
            orderId: (string) $event->orderId,
            rating: $event->rating,
            comment: $event->comment,
            isApproved: false, // По умолчанию не одобрен
            reply: null,
            metadata: $event->metadata,
            isDeleted: false,
            createdAt: now(),
            updatedAt: now()
        );
    }

    public function onReviewReplyAdded(ReviewReplyAdded $event): void
    {
        // Обновляем проекцию - добавляем ответ
        // В реальном проекте здесь бы был код для обновления read-модели
    }

    public function onReviewApproved(ReviewApproved $event): void
    {
        // Обновляем проекцию - отмечаем как одобренный
        // В реальном проекте здесь бы был код для обновления read-модели
    }
}
