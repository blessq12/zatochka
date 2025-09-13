<?php

namespace App\Application\UseCases\Review;

class GetReviewUseCase extends BaseReviewUseCase
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
        return $this->reviewRepository->get($this->data['id']);
    }
}
