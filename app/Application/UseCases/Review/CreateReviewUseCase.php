<?php

namespace App\Application\UseCases\Review;

use App\Domain\Review\Repository\ReviewRepository;
use App\Domain\Review\Event\ReviewCreated;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class CreateReviewUseCase extends BaseReviewUseCase
{
    private ?array $dto = null;

    public function validateSpecificData(): self
    {
        // Валидация обязательных полей
        $requiredFields = ['client_id', 'order_id', 'rating', 'comment'];
        foreach ($requiredFields as $field) {
            if (!isset($this->data[$field]) || empty($this->data[$field])) {
                throw new \InvalidArgumentException("Field {$field} is required");
            }
        }

        // Валидация рейтинга
        $rating = (int) $this->data['rating'];
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Rating must be between 1 and 5');
        }

        // Проверяем что отзыв по этому заказу еще не существует
        if ($this->reviewRepository->existsByOrderAndClient($this->data['order_id'], $this->data['client_id'])) {
            throw new \InvalidArgumentException('Review for this order already exists');
        }

        $this->dto = $this->data;
        return $this;
    }

    public function execute(): mixed
    {
        $review = $this->reviewRepository->create($this->dto);


        $aggregate = \App\Domain\Review\AggregateRoot\ReviewAggregateRoot::create();
        $aggregate->createReview(
            reviewId: (int) $review->getId(),
            clientId: $review->getClientId(),
            orderId: $review->getOrderId(),
            rating: $review->getRating(),
            comment: $review->getComment(),
            metadata: $review->getMetadata()
        );

        $aggregate->persist();

        return $review;
    }
}
