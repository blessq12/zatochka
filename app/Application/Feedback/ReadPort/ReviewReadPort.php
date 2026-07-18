<?php

namespace App\Application\Feedback\ReadPort;

use App\Application\Feedback\DTO\PublicReviewDTO;
use App\Application\Feedback\DTO\ReviewDTO;

interface ReviewReadPort
{
    public function findById(int $reviewId): ?ReviewDTO;

    public function findByOrderId(string $orderId): ?ReviewDTO;

    /** @return list<ReviewDTO> */
    public function listPending(): array;

    /** @return list<ReviewDTO> */
    public function listPublished(): array;

    /**
     * Published reviews for the public site (no internal ids beyond review id).
     *
     * @return list<PublicReviewDTO>
     */
    public function listPublishedPublic(?int $limit = null): array;

    public function averagePublishedRating(): ?string;
}
