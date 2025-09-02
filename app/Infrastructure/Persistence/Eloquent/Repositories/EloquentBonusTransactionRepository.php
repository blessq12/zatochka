<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Bonuses\Contracts\BonusTransactionRepository;
use App\Domain\Bonuses\BonusAmount;
use App\Domain\Bonuses\BonusTransaction;
use App\Domain\Bonuses\BonusTransactionType;
use App\Models\BonusAccount as EloquentBonusAccount;
use App\Models\BonusTransaction as EloquentBonusTransaction;

class EloquentBonusTransactionRepository implements BonusTransactionRepository
{
    public function save(BonusTransaction $transaction): void
    {
        $model = new EloquentBonusTransaction();
        $model->client_id = EloquentBonusAccount::findOrFail($transaction->getAccountId())->client_id;
        $model->order_id = $transaction->getOrderId();
        $model->type = match ($transaction->getType()) {
            BonusTransactionType::ACCRUE, BonusTransactionType::REVERT => 'earn',
            BonusTransactionType::REDEEM, BonusTransactionType::EXPIRE => 'spend',
            default => 'earn'
        };
        $model->amount = $transaction->getAmount()->toInt();
        $model->description = $transaction->getType();
        $model->idempotency_key = $transaction->getIdempotencyKey();
        $model->save();
    }

    public function existsByIdempotencyKey(string $key): bool
    {
        return EloquentBonusTransaction::where('idempotency_key', $key)->exists();
    }

    public function listByAccountId(int $accountId, int $limit = 100, int $offset = 0): array
    {
        $account = EloquentBonusAccount::findOrFail($accountId);
        $rows = EloquentBonusTransaction::where('client_id', $account->client_id)
            ->orderByDesc('id')
            ->skip($offset)
            ->take($limit)
            ->get();

        return $rows->map(function ($row) use ($account) {
            $type = in_array($row->type, ['spend']) ? BonusTransactionType::REDEEM : BonusTransactionType::ACCRUE;
            return BonusTransaction::create(
                accountId: (int) $account->id,
                type: $type,
                amount: BonusAmount::fromInt((int) $row->amount),
                orderId: $row->order_id,
                relatedTransactionId: null,
                idempotencyKey: (string) $row->description
            );
        })->all();
    }

    public function findById(string $transactionId): ?BonusTransaction
    {
        $row = EloquentBonusTransaction::find($transactionId);
        if (!$row) {
            return null;
        }
        $account = EloquentBonusAccount::where('client_id', $row->client_id)->first();
        if (!$account) {
            return null;
        }
        $type = in_array($row->type, ['spend']) ? BonusTransactionType::REDEEM : BonusTransactionType::ACCRUE;
        return BonusTransaction::create(
            accountId: (int) $account->id,
            type: $type,
            amount: BonusAmount::fromInt((int) $row->amount),
            orderId: $row->order_id,
            relatedTransactionId: null,
            idempotencyKey: (string) $row->description
        );
    }

    public function findByOrderAndType(int $orderId, string $type): ?BonusTransaction
    {
        $row = EloquentBonusTransaction::where('order_id', $orderId)
            ->where('type', $type === BonusTransactionType::REDEEM ? 'spend' : 'earn')
            ->first();
        if (!$row) {
            return null;
        }
        $account = EloquentBonusAccount::where('client_id', $row->client_id)->first();
        if (!$account) {
            return null;
        }
        $mappedType = in_array($row->type, ['spend']) ? BonusTransactionType::REDEEM : BonusTransactionType::ACCRUE;
        return BonusTransaction::create(
            accountId: (int) $account->id,
            type: $mappedType,
            amount: BonusAmount::fromInt((int) $row->amount),
            orderId: $row->order_id,
            relatedTransactionId: null,
            idempotencyKey: (string) $row->description
        );
    }
}
