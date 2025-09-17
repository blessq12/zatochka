<?php

namespace App\Infrastructure\Bonus\Repository;

use App\Domain\Bonus\Repository\BonusAccountRepository;
use App\Domain\Bonus\Entity\BonusAccount;
use App\Models\BonusAccount as BonusAccountModel;

class BonusAccountRepositoryImpl implements BonusAccountRepository
{
    public function existsByClientId(int $clientId): bool
    {
        return BonusAccountModel::where('client_id', $clientId)->exists();
    }

    public function create(int $clientId): BonusAccount
    {
        $model = BonusAccountModel::create([
            'client_id' => $clientId,
            'balance' => 0,
        ]);

        return new BonusAccount(
            id: $model->id,
            clientId: $model->client_id,
            balance: $model->balance,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }

    public function getByClientId(int $clientId): BonusAccount
    {
        $model = BonusAccountModel::where('client_id', $clientId)->first();
        return new BonusAccount(
            id: $model->id,
            clientId: $model->client_id,
            balance: $model->balance,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }
}
