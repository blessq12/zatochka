<?php

namespace App\Application\ClientPortal\QueryHandler;

use App\Application\ClientPortal\Query\GetClientReviewsQuery;
use App\Application\ClientPortal\Support\ClientLoader;
use App\Domain\ClientPortal\Entity\Review;
use App\Domain\ClientPortal\Repository\ReviewRepositoryInterface;

final class GetClientReviewsQueryHandler
{
    public function __construct(
        private ClientLoader $clientLoader,
        private ReviewRepositoryInterface $reviews,
    ) {}

    /**
     * @return list<Review>
     */
    public function handle(GetClientReviewsQuery $query): array
    {
        $this->clientLoader->load($query->clientId);

        return $this->reviews->findByClientId($query->clientId);
    }
}
