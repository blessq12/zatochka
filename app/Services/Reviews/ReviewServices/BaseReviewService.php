<?php

namespace App\Services\Reviews\ReviewServices;

abstract class BaseReviewService
{

    public function __construct() {}

    abstract public function getReviews(): array;
}
