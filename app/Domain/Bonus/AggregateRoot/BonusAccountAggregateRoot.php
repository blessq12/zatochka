<?php

namespace App\Domain\Bonus\AggregateRoot;

use App\Domain\Bonus\Event\BonusAccountCreated;
use App\Domain\Bonus\Event\BonusTransactionCreated;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class BonusAccountAggregateRoot extends AggregateRoot
{
    public function createAccount(int $clientId, int $initialBalance = 0): self
    {
        $this->recordThat(new BonusAccountCreated(
            clientId: $clientId,
            initialBalance: $initialBalance
        ));

        return $this;
    }

    public function addTransaction(string $transactionId, string $accountId, string $type, int $amount, string $description, ?string $orderId = null): self
    {
        $this->recordThat(new BonusTransactionCreated(
            transactionId: $transactionId,
            accountId: $accountId,
            type: $type,
            amount: $amount,
            description: $description,
            orderId: $orderId
        ));

        return $this;
    }
}
