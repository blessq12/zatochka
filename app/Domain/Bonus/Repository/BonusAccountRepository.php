<?php

namespace App\Domain\Bonus\Repository;

use App\Domain\Bonus\Entity\BonusAccount;

interface BonusAccountRepository
{
    public function existsByClientId(int $clientId): bool;

    public function create(int $clientId): BonusAccount;

    public function getByClientId(int $clientId): BonusAccount;
}
