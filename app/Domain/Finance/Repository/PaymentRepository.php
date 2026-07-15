<?php

namespace App\Domain\Finance\Repository;

use App\Domain\Finance\Entity\Payment;
use App\Shared\ValueObject\EntityId;

interface PaymentRepository
{
    public function save(Payment $payment): void;

    public function findById(EntityId $id): ?Payment;

    public function getById(EntityId $id): Payment;
}
