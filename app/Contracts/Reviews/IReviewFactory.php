<?php

namespace App\Contracts\Reviews;

use App\Services\Reviews\ReviewServices\BaseReviewService;

interface IReviewFactory
{
    public function callService(string $serviceName): BaseReviewService;
    public function callAllServices(): array;
}
