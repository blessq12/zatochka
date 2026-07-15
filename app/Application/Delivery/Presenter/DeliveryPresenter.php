<?php

namespace App\Application\Delivery\Presenter;

use App\Application\Delivery\DTO\DeliveryRequestDTO;

interface DeliveryPresenter
{
    /** @return array<string, mixed> */
    public function present(DeliveryRequestDTO $request): array;
}
