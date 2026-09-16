<?php

namespace App\Http\Controllers\Finance;

use App\Application\Finance\Command\RegisterCashOperationCommand;
use App\Application\Finance\Command\RegisterCashOperationHandler;
use App\Http\Controllers\Controller;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CashOperationController extends Controller
{
    public function __construct(
        private RegisterCashOperationHandler $registerCashOperation,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type');

        $query = \App\Infrastructure\Finance\Model\CashOperationModel::query()
            ->orderByDesc('registered_at')
            ->orderByDesc('id');

        if (is_string($type) && in_array($type, ['in', 'out'], true)) {
            $query->where('type', $type);
        }

        $items = $query->limit(200)->get()->map(fn ($op): array => [
            'id' => $op->id,
            'type' => $op->type,
            'paymentMethod' => $op->payment_method,
            'amount' => $op->amount,
            'currency' => $op->currency,
            'comment' => $op->comment,
            'paymentId' => $op->payment_id,
            'registeredAt' => $op->registered_at?->toIso8601String(),
        ]);

        $inSum = (string) \App\Infrastructure\Finance\Model\CashOperationModel::query()
            ->where('type', 'in')
            ->sum('amount');
        $outSum = (string) \App\Infrastructure\Finance\Model\CashOperationModel::query()
            ->where('type', 'out')
            ->sum('amount');

        return $this->ok([
            'items' => $items,
            'summary' => [
                'in' => $inSum,
                'out' => $outSum,
                'balance' => (string) ((float) $inSum - (float) $outSum),
            ],
        ]);
    }

    public function show(int $cashOperationId): JsonResponse
    {
        $op = \App\Infrastructure\Finance\Model\CashOperationModel::query()->find($cashOperationId);

        if ($op === null) {
            return response()->json(['message' => 'Операция не найдена.'], 404);
        }

        return $this->ok([
            'id' => $op->id,
            'type' => $op->type,
            'paymentMethod' => $op->payment_method,
            'amount' => $op->amount,
            'currency' => $op->currency,
            'comment' => $op->comment,
            'paymentId' => $op->payment_id,
            'registeredAt' => $op->registered_at?->toIso8601String(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'in:in,out'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'comment' => ['nullable', 'string'],
        ]);

        $cashOperationId = $this->ids->next('cash_operation')->value;

        $this->registerCashOperation->handle(new RegisterCashOperationCommand(
            $cashOperationId,
            $data['type'],
            (string) $data['amount'],
            $data['currency'] ?? 'RUB',
            $data['comment'] ?? null,
        ));

        return $this->created([
            'id' => $cashOperationId,
            'type' => $data['type'],
            'amount' => (string) $data['amount'],
            'currency' => $data['currency'] ?? 'RUB',
        ]);
    }
}
