<?php

namespace App\Http\Controllers\Pos;

use App\Application\OrderFulfillment\Command\AddWorkCommand;
use App\Application\OrderFulfillment\Command\MarkOrderReadyCommand;
use App\Application\OrderFulfillment\Command\MarkOrderWaitingForPartsCommand;
use App\Application\OrderFulfillment\Command\RemoveWorkCommand;
use App\Application\OrderFulfillment\Command\ResumeOrderCommand;
use App\Application\OrderFulfillment\Command\ReturnOrderToWorkCommand;
use App\Application\OrderFulfillment\Command\TakeOrderToWorkCommand;
use App\Application\OrderFulfillment\Command\UpdateInternalNotesCommand;
use App\Application\OrderFulfillment\CommandHandler\AddWorkHandler;
use App\Application\OrderFulfillment\CommandHandler\MarkOrderReadyHandler;
use App\Application\OrderFulfillment\CommandHandler\MarkOrderWaitingForPartsHandler;
use App\Application\OrderFulfillment\CommandHandler\RemoveWorkHandler;
use App\Application\OrderFulfillment\CommandHandler\ResumeOrderHandler;
use App\Application\OrderFulfillment\CommandHandler\ReturnOrderToWorkHandler;
use App\Application\OrderFulfillment\CommandHandler\TakeOrderToWorkHandler;
use App\Application\OrderFulfillment\CommandHandler\UpdateInternalNotesHandler;
use App\Application\OrderFulfillment\Presenter\PosOrderPresenter;
use App\Application\OrderFulfillment\Query\GetPosDashboardQuery;
use App\Application\OrderFulfillment\Query\GetPosOrderCountsQuery;
use App\Application\OrderFulfillment\Query\GetPosOrderDetailQuery;
use App\Application\OrderFulfillment\Query\GetPosOrdersQuery;
use App\Application\OrderFulfillment\QueryHandler\GetPosDashboardQueryHandler;
use App\Application\OrderFulfillment\QueryHandler\GetPosOrderCountsQueryHandler;
use App\Application\OrderFulfillment\QueryHandler\GetPosOrderDetailQueryHandler;
use App\Application\OrderFulfillment\QueryHandler\GetPosOrdersQueryHandler;
use App\Application\Equipment\Presenter\EquipmentPresenter;
use App\Application\Equipment\Query\GetEquipmentOrderHistoryQuery;
use App\Application\Equipment\Query\SearchEquipmentQuery;
use App\Application\Equipment\QueryHandler\GetEquipmentOrderHistoryQueryHandler;
use App\Application\Equipment\QueryHandler\SearchEquipmentQueryHandler;
use App\Application\Warehouse\Presenter\WarehouseItemPresenter;
use App\Application\Warehouse\Query\SearchWarehouseItemsQuery;
use App\Application\Warehouse\QueryHandler\SearchWarehouseItemsQueryHandler;
use App\Domain\OrderFulfillment\Enum\PosOrderListTab;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

final class PosController
{
    use RendersPosOrder;

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = UserModel::query()->where('email', $credentials['email'])->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Неверные учётные данные.'], 401);
        }

        $token = $user->createToken('pos')->plainTextToken;

        return response()->json([
            'token' => $token,
            'master' => [
                'id' => $user->id,
                'name' => trim($user->name.' '.$user->surname),
                'email' => $user->email,
            ],
        ]);
    }

    public function counts(Request $request, GetPosOrderCountsQueryHandler $handler): JsonResponse
    {
        $counts = $handler->handle(new GetPosOrderCountsQuery($this->masterId($request)));

        return response()->json(['data' => $counts]);
    }

    public function dashboard(Request $request, GetPosDashboardQueryHandler $handler): JsonResponse
    {
        $data = $handler->handle(new GetPosDashboardQuery($this->masterId($request)));

        return response()->json(['data' => $data]);
    }

    public function index(Request $request, GetPosOrdersQueryHandler $handler): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(PosOrderListTab::class)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $result = $handler->handle(new GetPosOrdersQuery(
            masterId: $this->masterId($request),
            tab: isset($validated['status']) ? PosOrderListTab::from($validated['status']) : null,
            page: (int) ($validated['page'] ?? 1),
            perPage: (int) ($validated['per_page'] ?? 20),
        ));

        return response()->json([
            'data' => PosOrderPresenter::list($result['items']),
            'meta' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'per_page' => $result['per_page'],
            ],
        ]);
    }

    public function show(int $orderId, Request $request, GetPosOrderDetailQueryHandler $handler): JsonResponse
    {
        $order = $handler->handle(new GetPosOrderDetailQuery(
            orderId: $orderId,
            masterId: $this->masterId($request),
        ));

        return $this->orderResponse($order);
    }

    public function takeToWork(int $orderId, Request $request, TakeOrderToWorkHandler $handler): JsonResponse
    {
        $order = $handler->handle(new TakeOrderToWorkCommand(
            orderId: $orderId,
            masterId: $this->masterId($request),
        ));

        return $this->orderResponse($order);
    }

    public function markWaitingForParts(int $orderId, Request $request, MarkOrderWaitingForPartsHandler $handler): JsonResponse
    {
        $order = $handler->handle(new MarkOrderWaitingForPartsCommand(
            orderId: $orderId,
            masterId: $this->masterId($request),
        ));

        return $this->orderResponse($order);
    }

    public function resume(int $orderId, Request $request, ResumeOrderHandler $handler): JsonResponse
    {
        $order = $handler->handle(new ResumeOrderCommand(
            orderId: $orderId,
            masterId: $this->masterId($request),
        ));

        return $this->orderResponse($order);
    }

    public function addWork(int $orderId, Request $request, AddWorkHandler $handler): JsonResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
        ]);

        $order = $handler->handle(new AddWorkCommand(
            orderId: $orderId,
            masterId: $this->masterId($request),
            description: $validated['description'],
        ));

        return $this->orderResponse($order);
    }

    public function removeWork(int $orderId, Request $request, RemoveWorkHandler $handler): JsonResponse
    {
        $validated = $request->validate([
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $order = $handler->handle(new RemoveWorkCommand(
            orderId: $orderId,
            masterId: $this->masterId($request),
            sortOrder: (int) $validated['sort_order'],
        ));

        return $this->orderResponse($order);
    }

    public function updateInternalNotes(int $orderId, Request $request, UpdateInternalNotesHandler $handler): JsonResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $order = $handler->handle(new UpdateInternalNotesCommand(
            orderId: $orderId,
            masterId: $this->masterId($request),
            notes: $validated['notes'] ?? null,
        ));

        return $this->orderResponse($order);
    }

    public function markReady(int $orderId, Request $request, MarkOrderReadyHandler $handler): JsonResponse
    {
        $order = $handler->handle(new MarkOrderReadyCommand(
            orderId: $orderId,
            masterId: $this->masterId($request),
        ));

        return $this->orderResponse($order);
    }

    public function returnToWork(int $orderId, Request $request, ReturnOrderToWorkHandler $handler): JsonResponse
    {
        $order = $handler->handle(new ReturnOrderToWorkCommand(
            orderId: $orderId,
            masterId: $this->masterId($request),
        ));

        return $this->orderResponse($order);
    }

    public function searchWarehouseItems(Request $request, SearchWarehouseItemsQueryHandler $handler): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $result = $handler->handle(new SearchWarehouseItemsQuery(
            query: $validated['query'] ?? null,
            page: (int) ($validated['page'] ?? 1),
            perPage: (int) ($validated['per_page'] ?? 20),
        ));

        return response()->json([
            'data' => WarehouseItemPresenter::list($result['items']),
            'meta' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'per_page' => $result['per_page'],
            ],
        ]);
    }

    public function searchEquipment(Request $request, SearchEquipmentQueryHandler $handler): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $result = $handler->handle(new SearchEquipmentQuery(
            query: $validated['query'] ?? null,
            page: (int) ($validated['page'] ?? 1),
            perPage: (int) ($validated['per_page'] ?? 20),
        ));

        return response()->json([
            'data' => EquipmentPresenter::list($result['items']),
            'meta' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'per_page' => $result['per_page'],
            ],
        ]);
    }

    public function equipmentOrderHistory(int $equipmentId, GetEquipmentOrderHistoryQueryHandler $handler): JsonResponse
    {
        $orders = $handler->handle(new GetEquipmentOrderHistoryQuery($equipmentId));

        return response()->json([
            'data' => array_map(PosOrderPresenter::listItem(...), $orders),
        ]);
    }

    private function masterId(Request $request): int
    {
        /** @var UserModel $user */
        $user = $request->user();

        return $user->id;
    }
}
