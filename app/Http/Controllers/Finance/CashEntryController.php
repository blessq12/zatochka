<?php

namespace App\Http\Controllers\Finance;

use App\Application\Finance\Command\DeleteManualCashEntryHandler;
use App\Application\Finance\Command\RecordManualCashEntryHandler;
use App\Application\Finance\Query\ListCashEntriesHandler;
use App\Http\Controllers\Controller;
use App\Shared\Domain\DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CashEntryController extends Controller
{
    public function __construct(
        private ListCashEntriesHandler $list,
        private RecordManualCashEntryHandler $record,
        private DeleteManualCashEntryHandler $delete,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'type' => ['nullable', 'in:income,expense'],
        ]);

        return response()->json($this->list->handle(
            $data['from'] ?? null,
            $data['to'] ?? null,
            $data['type'] ?? null,
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:0'],
            'occurred_at' => ['nullable', 'date'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $item = $this->record->handle(
            $data['type'],
            (string) $data['amount'],
            $data['occurred_at'] ?? null,
            $data['comment'] ?? null,
        );

        return response()->json($item->toArray(), 201);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->delete->handle($id);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(null, 204);
    }
}
