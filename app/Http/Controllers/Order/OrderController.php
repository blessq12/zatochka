<?php

namespace App\Http\Controllers\Order;

use App\Application\Order\Command\AssignMasterHandler;
use App\Application\Order\Command\CreateOrderHandler;
use App\Application\Order\Command\CreateOrderReviewHandler;
use App\Application\Order\Command\TransitionOrderStatusHandler;
use App\Application\Order\Command\UpdateOrderItemsHandler;
use App\Application\Order\Query\GetOrderHandler;
use App\Application\Order\Query\ListOrdersHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderController extends Controller
{
    public function __construct(
        private ListOrdersHandler $listOrders,
        private GetOrderHandler $getOrder,
        private CreateOrderHandler $createOrder,
        private UpdateOrderItemsHandler $updateItems,
        private AssignMasterHandler $assignMaster,
        private TransitionOrderStatusHandler $transitionStatus,
        private CreateOrderReviewHandler $createReview,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $asMaster = $request->attributes->get('actor_type') === 'masters';

        $clientId = $request->query('client_id');
        $clientId = $clientId !== null && $clientId !== '' ? (int) $clientId : null;
        $status = $request->query('status');
        $status = is_string($status) && $status !== '' ? $status : null;
        $masterId = $request->query('master_id');
        $masterId = $masterId !== null && $masterId !== '' ? (int) $masterId : null;
        $equipmentId = $request->query('equipment_id');
        $equipmentId = $equipmentId !== null && $equipmentId !== '' ? (int) $equipmentId : null;

        if ($asMaster) {
            if ($equipmentId === null || $equipmentId < 1) {
                return response()->json([
                    'message' => 'equipment_id is required.',
                ], 422);
            }
            $clientId = null;
            $masterId = null;
        }

        $items = $this->listOrders->handle($clientId, $status, $masterId, $equipmentId);

        return response()->json([
            'data' => array_map(static fn ($item) => $item->toArray(), $items),
        ]);
    }

    public function assigned(Request $request): JsonResponse
    {
        $masterId = (int) $request->attributes->get('actor_id');
        $items = $this->listOrders->handle(null, 'master_assigned', $masterId);

        return response()->json([
            'data' => array_map(static fn ($item) => $item->toArray(), $items),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $actorType = (string) $request->attributes->get('actor_type');
        $asClientId = $actorType === 'clients'
            ? (int) $request->attributes->get('actor_id')
            : null;
        $asMasterId = $actorType === 'masters'
            ? (int) $request->attributes->get('actor_id')
            : null;

        $item = $this->getOrder->handle($id, $asClientId, $asMasterId);
        if ($item === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($item->toArray());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id' => ['required', 'integer', 'min:1'],
            'billing_type' => ['required', 'string', 'in:paid,warranty'],
            'urgency' => ['nullable', 'string', 'in:normal,urgent'],
            'estimated_cost' => ['required', 'numeric', 'min:0'],
            'needs_delivery' => ['required', 'boolean'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.kind' => ['required', 'string', 'in:sharpening,repair'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.equipment_id' => ['nullable', 'integer', 'min:1'],
            'items.*.problem' => ['nullable', 'string'],
        ]);

        $item = $this->createOrder->handle(
            (int) $data['client_id'],
            $data['billing_type'],
            $data['urgency'] ?? 'normal',
            (string) $data['estimated_cost'],
            (bool) $data['needs_delivery'],
            $data['delivery_address'] ?? null,
            $data['items'],
        );

        return response()->json($item->toArray(), 201);
    }

    public function updateItems(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.kind' => ['required', 'string', 'in:sharpening,repair'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.equipment_id' => ['nullable', 'integer', 'min:1'],
            'items.*.problem' => ['nullable', 'string'],
        ]);

        $item = $this->updateItems->handle($id, $data['items']);
        if ($item === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($item->toArray());
    }

    public function assignMaster(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'master_id' => ['required', 'integer', 'min:1'],
        ]);

        $item = $this->assignMaster->handle($id, (int) $data['master_id']);
        if ($item === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($item->toArray());
    }

    public function transition(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $item = $this->transitionStatus->handle($id, $data['status']);
        if ($item === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($item->toArray());
    }

    public function storeReview(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'text' => ['nullable', 'string'],
        ]);

        $clientId = (int) $request->attributes->get('actor_id');
        $item = $this->createReview->handle(
            $id,
            $clientId,
            (int) $data['rating'],
            $data['text'] ?? null,
        );

        return response()->json($item->toArray(), 201);
    }
}
