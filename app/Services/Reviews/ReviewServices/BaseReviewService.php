<?php

namespace App\Services\Reviews\ReviewServices;

abstract class BaseReviewService
{
    abstract public function getReviews(): array;
}
