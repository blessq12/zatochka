<?php

namespace App\Services\Reviews\ReviewServices;

use App\Contracts\Reviews\IReviewService;
use App\Services\Reviews\ReviewServices\BaseReviewService;


class YandexService extends BaseReviewService implements IReviewService
{
    public function getReviews(): array
    {
        \Log::info('YandexService called from console');
        return [];
    }
}
