<?php

namespace App\Services\Reviews;

use App\Contracts\Reviews\IReviewFactory;

use App\Services\Reviews\ReviewServices\BaseReviewService;
use App\Services\Reviews\ReviewServices\TwoGisService;
use App\Services\Reviews\ReviewServices\YandexService;

class ReviewFactory implements IReviewFactory
{
    public function callService(string $serviceName): BaseReviewService
    {
        return app($serviceName);
    }

    public function callAllServices(): array
    {
        return [
            $this->callService(TwoGisService::class),
            $this->callService(YandexService::class),
        ];
    }
}
