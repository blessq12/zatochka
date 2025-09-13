<?php

namespace App\Domain\Review\Repository;

use App\Domain\Review\Entity\Review;

interface ReviewRepository
{
    public function create(array $data): Review;

    public function get(string $id): ?Review;

    public function update(Review $review, array $data): Review;

    public function delete(string $id): bool;

    public function findByOrder(string $orderId): array;

    public function findByClient(string $clientId): array;

    public function getApprovedReviews(): array;

    public function existsByOrderAndClient(string $orderId, string $clientId): bool;
}
