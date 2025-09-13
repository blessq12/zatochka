<?php

namespace App\Projectors;

use App\Domain\Bonus\Event\BonusAccountCreated;
use App\Domain\Bonus\Event\BonusTransactionCreated;
use App\Models\BonusAccount;
use App\Models\BonusTransaction;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class BonusProjector extends Projector
{
    public function onBonusAccountCreated(BonusAccountCreated $event): void
    {
        BonusAccount::create([
            'client_id' => $event->clientId,
            'balance' => $event->initialBalance
        ]);
    }

    public function onBonusTransactionCreated(BonusTransactionCreated $event): void
    {
        BonusTransaction::create([
            'id' => $event->transactionId,
            'bonus_account_id' => $event->accountId,
            'type' => $event->type,
            'amount' => $event->amount,
            'description' => $event->description,
            'order_id' => $event->orderId
        ]);
    }
}
