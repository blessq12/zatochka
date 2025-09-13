<?php

namespace App\Application\UseCases\Review;

class UpdateReviewUseCase extends BaseReviewUseCase
{
    public function validateSpecificData(): self
    {
        if (!isset($this->data['id']) || empty($this->data['id'])) {
            throw new \InvalidArgumentException('Review ID is required');
        }

        // Валидация рейтинга если передан
        if (isset($this->data['rating'])) {
            $rating = (int) $this->data['rating'];
            if ($rating < 1 || $rating > 5) {
                throw new \InvalidArgumentException('Rating must be between 1 and 5');
            }
        }

        return $this;
    }

    public function execute(): mixed
    {
        $review = $this->reviewRepository->get($this->data['id']);
        if (!$review) {
            throw new \InvalidArgumentException('Review not found');
        }

        return $this->reviewRepository->update($review, $this->data);
    }
}
