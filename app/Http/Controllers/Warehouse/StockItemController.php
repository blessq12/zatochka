<?php

namespace App\Http\Controllers\Warehouse;

use App\Application\Warehouse\Command\CreateStockItemHandler;
use App\Application\Warehouse\Command\ReceiveStockItemHandler;
use App\Application\Warehouse\Command\UpdateStockItemHandler;
use App\Application\Warehouse\Query\GetStockItemHandler;
use App\Application\Warehouse\Query\ListStockItemsHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class StockItemController extends Controller
{
    public function __construct(
        private ListStockItemsHandler $list,
        private GetStockItemHandler $get,
        private CreateStockItemHandler $create,
        private UpdateStockItemHandler $update,
        private ReceiveStockItemHandler $receive,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');
        $category = is_string($category) && $category !== '' ? $category : null;
        $items = $this->list->handle($category);

        return response()->json([
            'data' => array_map(static fn ($item) => $item->toArray(), $items),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $item = $this->get->handle($id);
        if ($item === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($item->toArray());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category' => ['required', Rule::in(['spare_part', 'consumable'])],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:32'],
            'qty_on_hand' => ['nullable', 'numeric', 'min:0'],
        ]);

        $item = $this->create->handle(
            $data['category'],
            $data['name'],
            $data['unit'],
            isset($data['qty_on_hand']) ? (string) $data['qty_on_hand'] : '0',
        );

        return response()->json($item->toArray(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'category' => ['required', Rule::in(['spare_part', 'consumable'])],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:32'],
        ]);

        $item = $this->update->handle(
            $id,
            $data['category'],
            $data['name'],
            $data['unit'],
        );

        if ($item === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($item->toArray());
    }

    public function receive(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'qty' => ['required', 'numeric', 'gt:0'],
        ]);

        $item = $this->receive->handle($id, (string) $data['qty']);
        if ($item === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($item->toArray());
    }
}
