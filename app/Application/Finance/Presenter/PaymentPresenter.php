<?php

namespace App\Application\Finance\Presenter;

use App\Application\Finance\DTO\PaymentDTO;

interface PaymentPresenter
{
    /** @return array<string, mixed> */
    public function present(PaymentDTO $payment): array;
}
