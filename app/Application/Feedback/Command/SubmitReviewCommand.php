<?php


final readonly class SubmitReviewCommand
{
    public function __construct(
        public int $reviewId,
        public string $orderId,
        public int $clientId,
        public int $rating,
        public ?string $comment = null,
    ) {}
}
