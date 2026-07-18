<?php

namespace App\Application\Feedback\Query;

use App\Application\Feedback\DTO\PublicReviewDTO;
use App\Application\Feedback\ReadPort\ReviewReadPort;

final readonly class ListPublishedReviewsHandler
{
    public function __construct(
        private ReviewReadPort $reviews,
    ) {}

    /**
     * @return array{
     *     average_rating: ?string,
     *     items: list<array<string, mixed>>
     * }
     */
    public function handle(?int $limit = null): array
    {
        $items = array_map(
            static fn (PublicReviewDTO $review): array => $review->toArray(),
            $this->reviews->listPublishedPublic($limit),
        );

        return [
            'average_rating' => $this->reviews->averagePublishedRating(),
            'items' => $items,
        ];
    }
}
