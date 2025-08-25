<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Order;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ReviewService
{
    /**
     * Создать отзыв
     */
    public function createReview(array $data): Review
    {
        return Review::create([
            'order_id' => $data['order_id'],
            'user_id' => $data['user_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'is_approved' => false,
        ]);
    }

    /**
     * Создать отзыв для заказа
     */
    public function createOrderReview(Order $order, Client $client, int $rating, ?string $comment = null): Review
    {
        return $this->createReview([
            'order_id' => $order->id,
            'user_id' => $client->id,
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }

    /**
     * Получить отзывы заказа
     */
    public function getOrderReviews(Order $order)
    {
        return $order->reviews()
            ->with('client')
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получить все одобренные отзывы
     */
    public function getApprovedReviews(int $limit = 10)
    {
        $cacheKey = "approved_reviews_{$limit}";

        return Cache::remember($cacheKey, 3600, function () use ($limit) {
            return Review::with(['client', 'order'])
                ->where('is_approved', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Получить отзывы клиента
     */
    public function getClientReviews(Client $client)
    {
        return $client->reviews()
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Одобрить отзыв
     */
    public function approveReview(Review $review): bool
    {
        return $review->update(['is_approved' => true]);
    }

    /**
     * Отклонить отзыв
     */
    public function rejectReview(Review $review): bool
    {
        return $review->update(['is_approved' => false]);
    }

    /**
     * Удалить отзыв
     */
    public function deleteReview(Review $review): bool
    {
        return $review->delete();
    }

    /**
     * Получить статистику отзывов
     */
    public function getReviewStats(): array
    {
        return Cache::remember('review_stats', 3600, function () {
            $totalReviews = Review::count();
            $approvedReviews = Review::where('is_approved', true)->count();
            $pendingReviews = Review::where('is_approved', false)->count();

            $averageRating = Review::where('is_approved', true)->avg('rating') ?? 0;

            $ratingDistribution = Review::where('is_approved', true)
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->orderBy('rating')
                ->pluck('count', 'rating')
                ->toArray();

            return [
                'total_reviews' => $totalReviews,
                'approved_reviews' => $approvedReviews,
                'pending_reviews' => $pendingReviews,
                'average_rating' => round($averageRating, 1),
                'rating_distribution' => $ratingDistribution,
            ];
        });
    }

    /**
     * Получить отзывы для модерации
     */
    public function getPendingReviews(int $limit = 20)
    {
        return Review::with(['client', 'order'])
            ->where('is_approved', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Проверить, может ли клиент оставить отзыв для заказа
     */
    public function canClientReviewOrder(Client $client, Order $order): bool
    {
        // Проверяем, что заказ принадлежит клиенту
        if ($order->client_id !== $client->id) {
            return false;
        }

        // Проверяем, что заказ завершен
        if ($order->status !== 'completed') {
            return false;
        }

        // Проверяем, что отзыв еще не оставлен
        $existingReview = Review::where('order_id', $order->id)
            ->where('user_id', $client->id)
            ->first();

        return !$existingReview;
    }

    /**
     * Отправить запрос на отзыв
     */
    public function sendReviewRequest(Order $order): bool
    {
        // Здесь можно добавить логику отправки уведомления
        // о запросе отзыва через Telegram или email

        return $order->update(['review_request_sent' => true]);
    }

    /**
     * Очистить кеш отзывов
     */
    public function clearCache(): void
    {
        Cache::forget('review_stats');
        Cache::flush();
    }
}
