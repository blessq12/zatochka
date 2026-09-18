<?php

namespace App\Infrastructure\Finance\Repository;

use App\Domain\Finance\Aggregate\CashEntry;
use App\Domain\Finance\CashEntrySource;
use App\Domain\Finance\CashEntryType;
use App\Domain\Finance\Repository\CashEntryRepository;
use App\Infrastructure\Finance\Eloquent\CashEntryModel;
use App\Shared\Domain\DomainException;
use DateTimeImmutable;

final class EloquentCashEntryRepository implements CashEntryRepository
{
    public function save(CashEntry $entry): CashEntry
    {
        if (
            $entry->source() === CashEntrySource::OrderIssue
            && $entry->orderId() !== null
            && $entry->id() === null
            && $this->findOrderIssueByOrderId($entry->orderId()) !== null
        ) {
            throw new DomainException('Cash income for this order already recorded.');
        }

        /** @var CashEntryModel $model */
        $model = $entry->id() === null
            ? new CashEntryModel()
            : CashEntryModel::query()->findOrFail($entry->id());

        $model->type = $entry->type()->value;
        $model->amount = $entry->amount();
        $model->occurred_at = $entry->occurredAt()->format('Y-m-d H:i:s');
        $model->source = $entry->source()->value;
        $model->order_id = $entry->orderId();
        $model->comment = $entry->comment();
        $model->save();

        if ($entry->id() === null) {
            $entry->assignId((int) $model->id);
        }

        return $entry;
    }

    public function findById(int $id): ?CashEntry
    {
        /** @var CashEntryModel|null $model */
        $model = CashEntryModel::query()->find($id);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findOrderIssueByOrderId(int $orderId): ?CashEntry
    {
        /** @var CashEntryModel|null $model */
        $model = CashEntryModel::query()
            ->where('source', CashEntrySource::OrderIssue->value)
            ->where('order_id', $orderId)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function delete(CashEntry $entry): void
    {
        if ($entry->id() === null) {
            return;
        }
        if (! $entry->isManual()) {
            throw new DomainException('Only manual cash entries can be deleted.');
        }
        CashEntryModel::query()->whereKey($entry->id())->delete();
    }

    public function list(
        ?DateTimeImmutable $from = null,
        ?DateTimeImmutable $to = null,
        ?CashEntryType $type = null,
    ): array {
        $query = CashEntryModel::query()->orderByDesc('occurred_at')->orderByDesc('id');

        if ($from !== null) {
            $query->where('occurred_at', '>=', $from->format('Y-m-d H:i:s'));
        }
        if ($to !== null) {
            $query->where('occurred_at', '<=', $to->format('Y-m-d H:i:s'));
        }
        if ($type !== null) {
            $query->where('type', $type->value);
        }

        return $query->get()->map(fn (CashEntryModel $model): CashEntry => $this->toDomain($model))->all();
    }

    public function summarize(
        ?DateTimeImmutable $from = null,
        ?DateTimeImmutable $to = null,
    ): array {
        $query = CashEntryModel::query();
        if ($from !== null) {
            $query->where('occurred_at', '>=', $from->format('Y-m-d H:i:s'));
        }
        if ($to !== null) {
            $query->where('occurred_at', '<=', $to->format('Y-m-d H:i:s'));
        }

        $income = (float) (clone $query)->where('type', CashEntryType::Income->value)->sum('amount');
        $expense = (float) (clone $query)->where('type', CashEntryType::Expense->value)->sum('amount');
        $net = $income - $expense;

        $balanceIncome = (float) CashEntryModel::query()->where('type', CashEntryType::Income->value)->sum('amount');
        $balanceExpense = (float) CashEntryModel::query()->where('type', CashEntryType::Expense->value)->sum('amount');
        $balance = $balanceIncome - $balanceExpense;

        return [
            'income' => number_format($income, 2, '.', ''),
            'expense' => number_format($expense, 2, '.', ''),
            'net' => number_format($net, 2, '.', ''),
            'balance' => number_format($balance, 2, '.', ''),
        ];
    }

    private function toDomain(CashEntryModel $model): CashEntry
    {
        return new CashEntry(
            (int) $model->id,
            CashEntryType::from((string) $model->type),
            (string) $model->amount,
            new DateTimeImmutable($model->occurred_at->format('Y-m-d H:i:s')),
            CashEntrySource::from((string) $model->source),
            $model->order_id !== null ? (int) $model->order_id : null,
            $model->comment !== null ? (string) $model->comment : null,
        );
    }
}
