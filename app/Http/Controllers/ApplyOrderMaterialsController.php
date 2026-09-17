<?php

namespace App\Http\Controllers;

use App\Application\Finance\Command\ReplaceOrderMaterialLinesHandler;
use App\Application\Order\Query\GetOrderHandler;
use App\Application\Warehouse\Command\ReplaceOrderIssueHandler;
use App\Shared\Domain\DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Composition root: Warehouse issue + Finance material lines for one order.
 * Does not live inside either BC Application layer.
 */
final class ApplyOrderMaterialsController extends Controller
{
    public function __construct(
        private GetOrderHandler $getOrder,
        private ReplaceOrderIssueHandler $replaceIssue,
        private ReplaceOrderMaterialLinesHandler $replaceMaterialLines,
    ) {}

    public function __invoke(Request $request, int $orderId): JsonResponse
    {
        $data = $request->validate([
            'lines' => ['required', 'array'],
            'lines.*.stock_item_id' => ['required', 'integer', 'min:1'],
            'lines.*.qty' => ['required', 'numeric', 'gt:0'],
            'lines.*.amount' => ['required', 'numeric', 'min:0'],
        ]);

        $order = $this->getOrder->handle($orderId);
        if ($order === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        if ($order->status !== 'works_completed') {
            throw new DomainException('Materials can be applied only when order is works_completed.');
        }

        $issueLines = [];
        $materialLines = [];
        foreach ($data['lines'] as $row) {
            $issueLines[] = [
                'stock_item_id' => (int) $row['stock_item_id'],
                'qty' => (string) $row['qty'],
            ];
            $materialLines[] = [
                'stock_item_id' => (int) $row['stock_item_id'],
                'amount' => (string) $row['amount'],
            ];
        }

        $result = DB::transaction(function () use ($orderId, $issueLines, $materialLines): array {
            $issue = $this->replaceIssue->handle($orderId, $issueLines);
            $pricing = $this->replaceMaterialLines->handle($orderId, $materialLines);

            return [
                'issue' => $issue->toArray(),
                'pricing' => $pricing->toArray(),
            ];
        });

        return response()->json($result);
    }
}
