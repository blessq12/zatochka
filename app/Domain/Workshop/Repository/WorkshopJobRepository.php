<?php

namespace App\Domain\Workshop\Repository;

use App\Domain\Workshop\Aggregate\WorkshopJob;

interface WorkshopJobRepository
{
    public function save(WorkshopJob $job): WorkshopJob;

    public function findById(int $id): ?WorkshopJob;

    public function findByOrderId(int $orderId): ?WorkshopJob;

    /**
     * @return list<WorkshopJob>
     */
    public function findOpenByMasterId(int $masterId): array;
}
