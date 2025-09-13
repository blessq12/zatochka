<?php

namespace App\Reactors;

use App\Domain\Review\Event\ReviewCreated;
use App\Domain\Review\Event\ReviewReplyAdded;
use App\Domain\Review\Event\ReviewApproved;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;
use Illuminate\Support\Facades\Log;

class ReviewReactor extends Reactor
{
    public function onReviewCreated(ReviewCreated $event): void
    {
        try {
            // Логируем успешное создание отзыва
            Log::info('Review created successfully', [
                'review_id' => $event->reviewId,
                'client_id' => $event->clientId,
                'order_id' => $event->orderId,
                'rating' => $event->rating,
                'comment' => substr($event->comment, 0, 100) . '...',
                'metadata' => $event->metadata,
                'timestamp' => now()->toISOString(),
                'source' => 'ReviewReactor'
            ]);

            // TODO: Отправка уведомления менеджеру о новом отзыве
            // TODO: Проверка на негативные отзывы для автоматического флага
            // TODO: Обновление статистики клиента
            // TODO: Интеграция с внешними системами отзывов

            Log::info('ReviewReactor: Successfully processed ReviewCreated event', [
                'review_id' => $event->reviewId,
                'event_type' => 'ReviewCreated',
                'processed_at' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('ReviewReactor: Failed to process ReviewCreated event', [
                'review_id' => $event->reviewId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function onReviewReplyAdded(ReviewReplyAdded $event): void
    {
        try {
            Log::info('Review reply added successfully', [
                'review_id' => $event->reviewId,
                'reply' => substr($event->reply, 0, 100) . '...',
                'replied_by' => $event->repliedBy,
                'timestamp' => now()->toISOString(),
                'source' => 'ReviewReactor'
            ]);

            // TODO: Отправка уведомления клиенту об ответе
            // TODO: Уведомление в Telegram канал

            Log::info('ReviewReactor: Successfully processed ReviewReplyAdded event', [
                'review_id' => $event->reviewId,
                'event_type' => 'ReviewReplyAdded',
                'processed_at' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('ReviewReactor: Failed to process ReviewReplyAdded event', [
                'review_id' => $event->reviewId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function onReviewApproved(ReviewApproved $event): void
    {
        try {
            Log::info('Review approved successfully', [
                'review_id' => $event->reviewId,
                'approved_by' => $event->approvedBy,
                'timestamp' => now()->toISOString(),
                'source' => 'ReviewReactor'
            ]);

            // TODO: Отправка уведомления клиенту об одобрении отзыва
            // TODO: Публикация отзыва на сайте
            // TODO: Обновление рейтинга сервиса

            Log::info('ReviewReactor: Successfully processed ReviewApproved event', [
                'review_id' => $event->reviewId,
                'event_type' => 'ReviewApproved',
                'processed_at' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('ReviewReactor: Failed to process ReviewApproved event', [
                'review_id' => $event->reviewId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
