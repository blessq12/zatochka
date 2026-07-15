<?php

namespace App\Http\Controllers\Inventory;

use App\Application\Inventory\Command\ChangeStockCommand;
use App\Application\Inventory\Command\ChangeStockHandler;
use App\Application\Inventory\Command\OpenStockItemCommand;
use App\Application\Inventory\Command\OpenStockItemHandler;
use App\Application\Inventory\Command\ReceiveMaterialCommand;
use App\Application\Inventory\Command\ReceiveMaterialHandler;
use App\Application\Inventory\Command\WriteOffMaterialCommand;
use App\Application\Inventory\Command\WriteOffMaterialHandler;
use App\Application\Inventory\Query\GetStockItemByIdHandler;
use App\Application\Inventory\Query\GetStockItemByIdQuery;
use App\Http\Controllers\Controller;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StockItemController extends Controller
{
    public function __construct(
        private OpenStockItemHandler $openStockItem,
        private ReceiveMaterialHandler $receiveMaterial,
        private WriteOffMaterialHandler $writeOffMaterial,
        private ChangeStockHandler $changeStock,
        private GetStockItemByIdHandler $getStockItemById,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'string'],
            'name' => ['required', 'string'],
            'unit' => ['required', 'string'],
            'initialQuantity' => ['nullable', 'numeric', 'min:0'],
        ]);

        $stockItemId = $this->ids->next('stock_item')->value;
        $materialId = $this->ids->next('material')->value;

        $this->openStockItem->handle(new OpenStockItemCommand(
            $stockItemId,
            $materialId,
            $data['sku'],
            $data['name'],
            $data['unit'],
            isset($data['initialQuantity']) ? (string) $data['initialQuantity'] : '0',
        ));

        return $this->created($this->getStockItemById->handle(new GetStockItemByIdQuery($stockItemId)));
    }

    public function show(int $stockItemId): JsonResponse
    {
        $item = $this->getStockItemById->handle(new GetStockItemByIdQuery($stockItemId));

        if ($item === null) {
            return response()->json(['message' => 'Stock item not found.'], 404);
        }

        return $this->ok($item);
    }

    public function receive(Request $request, int $stockItemId): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'gt:0'],
            'comment' => ['nullable', 'string'],
        ]);

        $this->receiveMaterial->handle(new ReceiveMaterialCommand(
            $stockItemId,
            $this->ids->next('warehouse_movement')->value,
            (string) $data['quantity'],
            $data['comment'] ?? null,
        ));

        return $this->ok($this->getStockItemById->handle(new GetStockItemByIdQuery($stockItemId)));
    }

    public function writeOff(Request $request, int $stockItemId): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'gt:0'],
            'comment' => ['nullable', 'string'],
        ]);

        $this->writeOffMaterial->handle(new WriteOffMaterialCommand(
            $stockItemId,
            $this->ids->next('warehouse_movement')->value,
            (string) $data['quantity'],
            $data['comment'] ?? null,
        ));

        return $this->ok($this->getStockItemById->handle(new GetStockItemByIdQuery($stockItemId)));
    }

    public function change(Request $request, int $stockItemId): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0'],
            'comment' => ['nullable', 'string'],
        ]);

        $this->changeStock->handle(new ChangeStockCommand(
            $stockItemId,
            $this->ids->next('warehouse_movement')->value,
            (string) $data['quantity'],
            $data['comment'] ?? null,
        ));

        return $this->ok($this->getStockItemById->handle(new GetStockItemByIdQuery($stockItemId)));
    }
}
