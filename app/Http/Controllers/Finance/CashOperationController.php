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
