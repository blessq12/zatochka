<?php

namespace App\Application\UseCases\Review;

class DeleteReviewUseCase extends BaseReviewUseCase
{
    public function validateSpecificData(): self
    {
        if (!isset($this->data['id']) || empty($this->data['id'])) {
            throw new \InvalidArgumentException('Review ID is required');
        }

        return $this;
    }

    public function execute(): mixed
    {
        $review = $this->reviewRepository->get($this->data['id']);
        if (!$review) {
            throw new \InvalidArgumentException('Review not found');
        }

        return $this->reviewRepository->delete($this->data['id']);
    }
}
