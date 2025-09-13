<?php

namespace App\Domain\Review\Mapper;

use App\Models\Review;

interface ReviewMapper
{
    public function toDomain(Review $model): \App\Domain\Review\Entity\Review;

    public function toEloquent(\App\Domain\Review\Entity\Review $entity): Review;

    public function fromArray(array $data): \App\Domain\Review\Entity\Review;
}
