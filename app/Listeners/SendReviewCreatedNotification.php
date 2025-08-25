<?php

namespace App\Listeners;

use App\Events\ReviewCreated;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendReviewCreatedNotification
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ReviewCreated $event): void
    {
        $review = $event->review;

        // Создаем уведомление для администратора
        Notification::create([
            'client_id' => $review->user_id,
            'order_id' => $review->order_id,
            'type' => 'review_request',
            'message_text' => "Новый отзыв для заказа {$review->order->order_number} с рейтингом {$review->rating}",
        ]);

        Log::info("Review created", [
            'review_id' => $review->id,
            'order_id' => $review->order_id,
            'rating' => $review->rating,
        ]);
    }
}
