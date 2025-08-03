<?php

namespace App\Services\Reviews\ReviewServices;

use App\Contracts\Reviews\IReviewService;
use App\Services\Reviews\ReviewServices\BaseReviewService;


class YandexService extends BaseReviewService implements IReviewService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getReviews(): array
    {
        return [
            'reviews' => [
                'review1' => 'review1',
                'review2' => 'review2',
                'review3' => 'review3',
            ],
        ];
    }
}
