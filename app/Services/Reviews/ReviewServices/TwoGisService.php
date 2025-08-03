<?php

namespace App\Services\Reviews\ReviewServices;

use App\Contracts\Reviews\IReviewService;
use App\Services\Reviews\ReviewServices\BaseReviewService;

class TwoGisService extends BaseReviewService implements IReviewService
{
    public function getReviews(): array
    {
        \Log::info('TwoGisService called from console');
        return [];
    }
}
