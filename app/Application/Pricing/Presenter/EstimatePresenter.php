<?php

namespace App\Application\Pricing\Presenter;

use App\Application\Pricing\DTO\EstimateDTO;

interface EstimatePresenter
{
    /** @return array<string, mixed> */
    public function present(EstimateDTO $estimate): array;
}
