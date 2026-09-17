<?php

namespace App\Http\Controllers\Finance;

use App\Application\Finance\Command\CancelEarningsGoalHandler;
use App\Application\Finance\Command\CreateEarningsGoalHandler;
use App\Application\Finance\Query\ListEarningsGoalsHandler;
use App\Http\Controllers\Controller;
use App\Shared\Domain\DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EarningsGoalController extends Controller
{
    public function __construct(
        private ListEarningsGoalsHandler $list,
        private CreateEarningsGoalHandler $create,
        private CancelEarningsGoalHandler $cancel,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->list->handle()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'target_amount' => ['required', 'numeric', 'gt:0'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $item = $this->create->handle(
            (string) $data['target_amount'],
            $data['starts_at'],
            $data['ends_at'],
            $data['title'] ?? null,
        );

        return response()->json($item->toArray(), 201);
    }

    public function cancel(int $id): JsonResponse
    {
        try {
            $item = $this->cancel->handle($id);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($item->toArray());
    }
}
