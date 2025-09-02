<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Bonuses\Contracts\BonusAccountRepository;
use App\Domain\Bonuses\BonusAccount as DomainBonusAccount;
use App\Models\BonusAccount as EloquentBonusAccount;

class EloquentBonusAccountRepository implements BonusAccountRepository
{
    public function findByClientId(int $clientId): ?DomainBonusAccount
    {
        $model = EloquentBonusAccount::where('client_id', $clientId)->first();
        if (!$model) {
            return null;
        }
        return DomainBonusAccount::restore($model->id, $model->client_id, (int) $model->balance);
    }

    public function findById(int $accountId): ?DomainBonusAccount
    {
        $model = EloquentBonusAccount::find($accountId);
        if (!$model) {
            return null;
        }
        return DomainBonusAccount::restore($model->id, $model->client_id, (int) $model->balance);
    }

    public function save(DomainBonusAccount $account): void
    {
        if ($account->getId() === 0) {
            $model = new EloquentBonusAccount();
            $model->client_id = $account->getClientId();
        } else {
            $model = EloquentBonusAccount::findOrFail($account->getId());
        }
        $model->balance = $account->getBalance()->toInt();
        $model->save();

        if ($account->getId() === 0) {
            $ref = new \ReflectionClass($account);
            $prop = $ref->getProperty('id');
            $prop->setAccessible(true);
            $prop->setValue($account, (int) $model->id);
        }
    }
}
