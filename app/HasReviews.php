<?php

namespace App;

use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasReviews
{
    /**
     * Получить все отзывы для этой модели
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'entity');
    }

    /**
     * Получить одобренные отзывы
     */
    public function approvedReviews(): MorphMany
    {
        return $this->reviews()->approved();
    }

    /**
     * Получить отзывы определенного типа
     */
    public function reviewsOfType(string $type): MorphMany
    {
        return $this->reviews()->ofType($type);
    }

    /**
     * Получить одобренные отзывы определенного типа
     */
    public function approvedReviewsOfType(string $type): MorphMany
    {
        return $this->reviews()->ofType($type)->approved();
    }

    /**
     * Получить средний рейтинг
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->approvedReviews()
            ->withRating()
            ->avg('rating') ?? 0;
    }

    /**
     * Получить количество отзывов
     */
    public function getReviewsCountAttribute(): int
    {
        return $this->approvedReviews()->count();
    }

    /**
     * Получить количество отзывов определенного типа
     */
    public function getReviewsCountOfType(string $type): int
    {
        return $this->approvedReviewsOfType($type)->count();
    }

    /**
     * Создать отзыв для этой модели
     */
    public function createReview(array $data): Review
    {
        $data['entity_type'] = get_class($this);
        $data['entity_id'] = $this->id;

        return Review::create($data);
    }

    /**
     * Получить отзывы с рейтингом
     */
    public function getReviewsWithRating(): MorphMany
    {
        return $this->approvedReviews()->withRating();
    }

    /**
     * Получить статистику отзывов
     */
    public function getReviewsStats(): array
    {
        $reviews = $this->approvedReviews();

        return [
            'total' => $reviews->count(),
            'with_rating' => $reviews->withRating()->count(),
            'average_rating' => $reviews->withRating()->avg('rating') ?? 0,
            'rating_distribution' => $this->getRatingDistribution(),
        ];
    }

    /**
     * Получить распределение рейтингов
     */
    public function getRatingDistribution(): array
    {
        $distribution = [];

        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $this->approvedReviews()
                ->where('rating', $i)
                ->count();
        }

        return $distribution;
    }
}
