<?php

namespace App\Http\Controllers\Order;

use App\Application\Order\Command\AssignMasterHandler;
use App\Application\Order\Command\CreateOrderHandler;
use App\Application\Order\Command\CreateOrderReviewHandler;
use App\Application\Order\Command\TransitionOrderStatusHandler;
use App\Application\Order\Command\UpdateOrderItemsHandler;
use App\Application\Order\Query\GetOrderHandler;
use App\Application\Order\Query\ListOrdersHandler;
use App\Domain\Order\OrderStatus;
use App\Http\Controllers\Controller;
use App\Shared\Domain\DomainException;
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
        $actorType = (string) $request->attributes->get('actor_type');
        $asMaster = $actorType === 'masters';
        $asClient = $actorType === 'clients';

        $clientId = $request->query('client_id');
        $clientId = $clientId !== null && $clientId !== '' ? (int) $clientId : null;
        $status = $request->query('status');
        $status = is_string($status) && $status !== '' ? $status : null;
        $masterId = $request->query('master_id');
        $masterId = $masterId !== null && $masterId !== '' ? (int) $masterId : null;
        $equipmentId = $request->query('equipment_id');
        $equipmentId = $equipmentId !== null && $equipmentId !== '' ? (int) $equipmentId : null;
        $statusesIn = null;
        $statusesNotIn = null;

        if ($asClient) {
            $clientId = (int) $request->attributes->get('actor_id');
            $masterId = null;
            $equipmentId = null;
            $status = null;
            [$statusesIn, $statusesNotIn] = $this->clientScopeFilters($request->query('scope'));
        } elseif ($asMaster) {
            if ($equipmentId === null || $equipmentId < 1) {
                return response()->json([
                    'message' => 'equipment_id is required.',
                ], 422);
            }
            $clientId = null;
            $masterId = null;
        }

        $items = $this->listOrders->handle(
            $clientId,
            $status,
            $masterId,
            $equipmentId,
            $statusesIn,
            $statusesNotIn,
        );

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

        if ($asClientId !== null && $item->status === OrderStatus::Cancelled->value) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($item->toArray());
    }

    public function store(Request $request): JsonResponse
    {
        $actorType = (string) $request->attributes->get('actor_type');
        $asClient = $actorType === 'clients';

        $rules = [
            'billing_type' => ['required', 'string', 'in:paid,warranty'],
            'urgency' => ['nullable', 'string', 'in:normal,urgent'],
            'estimated_cost' => [$asClient ? 'nullable' : 'required', 'numeric', 'min:0'],
            'needs_delivery' => ['required', 'boolean'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.kind' => ['required', 'string', 'in:sharpening,repair'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.equipment_id' => ['nullable', 'integer', 'min:1'],
            'items.*.problem' => ['nullable', 'string'],
        ];

        if (! $asClient) {
            $rules['client_id'] = ['required', 'integer', 'min:1'];
        }

        $data = $request->validate($rules);

        $clientId = $asClient
            ? (int) $request->attributes->get('actor_id')
            : (int) $data['client_id'];

        $item = $this->createOrder->handle(
            $clientId,
            $data['billing_type'],
            $data['urgency'] ?? 'normal',
            isset($data['estimated_cost']) ? (string) $data['estimated_cost'] : '0',
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

    /**
     * @return array{0: list<string>|null, 1: list<string>|null}
     */
    private function clientScopeFilters(mixed $scope): array
    {
        $scope = is_string($scope) ? $scope : 'active';

        return match ($scope) {
            'archive' => [[OrderStatus::Issued->value], null],
            'active' => [null, [OrderStatus::Issued->value, OrderStatus::Cancelled->value]],
            default => throw new DomainException('scope must be active or archive.'),
        };
    }
}
