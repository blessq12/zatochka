<?php

declare(strict_types=1);

namespace App\Domain\Bonuses\Contracts;

use App\Domain\Bonuses\BonusAccount;

interface BonusAccountRepository
{
    public function findByClientId(int $clientId): ?BonusAccount;

    public function findById(int $accountId): ?BonusAccount;

    public function save(BonusAccount $account): void;
}
