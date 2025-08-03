<?php

namespace App\Contracts\Reviews\ReviewServices;

interface IYandexService
{
    public function getReviews(): array;
}
