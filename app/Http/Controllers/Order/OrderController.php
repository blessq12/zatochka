<?php

namespace App\Http\Controllers\Order;

use App\Application\Order\Command\CancelOrderCommand;
use App\Application\Order\Command\CancelOrderHandler;
use App\Application\Order\Command\CloseOrderCommand;
use App\Application\Order\Command\CloseOrderHandler;
use App\Application\Order\Command\CompleteReceptionCommand;
use App\Application\Order\Command\CompleteReceptionHandler;
use App\Application\Order\Command\CreateOrderCommand;
use App\Application\Order\Command\CreateOrderHandler;
use App\Application\Order\Command\IssueOrderCommand;
use App\Application\Order\Command\IssueOrderHandler;
use App\Application\Order\DTO\CreateOrderItemDTO;
use App\Application\Order\DTO\ReceptionItemDTO;
use App\Application\Order\Query\GetOrderByIdHandler;
use App\Application\Order\Query\GetOrderByIdQuery;
use App\Http\Controllers\Controller;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderController extends Controller
{
    public function __construct(
        private CreateOrderHandler $createOrder,
        private CompleteReceptionHandler $completeReception,
        private CancelOrderHandler $cancelOrder,
        private CloseOrderHandler $closeOrder,
        private IssueOrderHandler $issueOrder,
        private GetOrderByIdHandler $getOrderById,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'clientId' => ['required', 'integer'],
            'estimatedAmount' => ['required', 'numeric'],
            'estimatedCurrency' => ['nullable', 'string', 'size:3'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.clientEquipmentId' => ['required', 'integer'],
        ]);

        $orderId = $this->ids->next('order')->value;
        $items = [];

        foreach ($data['items'] as $item) {
            $items[] = new CreateOrderItemDTO(
                $this->ids->next('order_item')->value,
                (int) $item['clientEquipmentId'],
            );
        }

        $this->createOrder->handle(new CreateOrderCommand(
            $orderId,
            (int) $data['clientId'],
            (string) $data['estimatedAmount'],
            $items,
            $data['estimatedCurrency'] ?? 'RUB',
        ));

        return $this->created($this->getOrderById->handle(new GetOrderByIdQuery($orderId)));
    }

    public function show(int $orderId): JsonResponse
    {
        $order = $this->getOrderById->handle(new GetOrderByIdQuery($orderId));

        if ($order === null) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return $this->ok($order);
    }

    public function completeReception(Request $request, int $orderId): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.orderItemId' => ['required', 'integer'],
            'items.*.conditionDescription' => ['required', 'string'],
            'items.*.visualNotes' => ['nullable', 'string'],
            'items.*.attachmentRefs' => ['nullable', 'array'],
            'items.*.attachmentRefs.*' => ['string'],
        ]);

        $items = [];

        foreach ($data['items'] as $item) {
            $items[] = new ReceptionItemDTO(
                (int) $item['orderItemId'],
                $this->ids->next('reception')->value,
                $item['conditionDescription'],
                $item['visualNotes'] ?? null,
                $item['attachmentRefs'] ?? [],
            );
        }

        $this->completeReception->handle(new CompleteReceptionCommand($orderId, $items));

        return $this->ok($this->getOrderById->handle(new GetOrderByIdQuery($orderId)));
    }

    public function cancel(Request $request, int $orderId): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $this->cancelOrder->handle(new CancelOrderCommand($orderId, $data['reason']));

        return $this->ok($this->getOrderById->handle(new GetOrderByIdQuery($orderId)));
    }

    public function close(int $orderId): JsonResponse
    {
        $this->closeOrder->handle(new CloseOrderCommand($orderId));

        return $this->ok($this->getOrderById->handle(new GetOrderByIdQuery($orderId)));
    }

    public function issue(int $orderId): JsonResponse
    {
        $this->issueOrder->handle(new IssueOrderCommand($orderId));

        return $this->ok($this->getOrderById->handle(new GetOrderByIdQuery($orderId)));
    }
}
