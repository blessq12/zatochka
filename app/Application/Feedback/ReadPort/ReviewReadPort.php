<?php


use App\Application\Feedback\DTO\ReviewDTO;

interface ReviewReadPort
{
    public function findById(int $reviewId): ?ReviewDTO;

    public function findByOrderId(string $orderId): ?ReviewDTO;

    /** @return list<ReviewDTO> */
    public function listPending(): array;

    /** @return list<ReviewDTO> */
    public function listPublished(): array;

    public function averagePublishedRating(): ?string;
}
