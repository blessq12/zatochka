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
use App\Domain\Order\VO\OrderId;
use App\Http\Controllers\Controller;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'clientId' => ['nullable', 'integer'],
            'newClientName' => ['nullable', 'string'],
            'newClientPhone' => ['nullable', 'string'],
            'newClientEmail' => ['nullable', 'email'],
            'estimatedAmount' => ['required', 'numeric'],
            'serviceType' => ['required', Rule::in(['sharpening', 'repair'])],
            'billingType' => ['required', Rule::in(['paid', 'warranty'])],
            'urgency' => ['required', Rule::in(['normal', 'urgent'])],
            'warrantySourceOrderId' => ['nullable', 'string', 'size:32'],
            'deliveryRequired' => ['nullable', 'boolean'],
            'defects' => ['nullable', 'string'],
            'internalNotes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.clientEquipmentId' => ['nullable', 'integer'],
            'items.*.toolName' => ['nullable', 'string'],
            'items.*.toolType' => ['nullable', 'string'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.equipmentTitle' => ['nullable', 'string'],
            'items.*.equipmentBrand' => ['nullable', 'string'],
            'items.*.equipmentModelName' => ['nullable', 'string'],
            'items.*.equipmentNotes' => ['nullable', 'string'],
        ]);

        $orderId = OrderId::generate()->value;
        $items = [];

        foreach ($data['items'] as $item) {
            $items[] = new CreateOrderItemDTO(
                $this->ids->next('order_item')->value,
                isset($item['clientEquipmentId']) ? (int) $item['clientEquipmentId'] : null,
                $item['toolName'] ?? null,
                $item['toolType'] ?? null,
                isset($item['quantity']) ? (int) $item['quantity'] : null,
                $item['equipmentTitle'] ?? null,
                $item['equipmentBrand'] ?? null,
                $item['equipmentModelName'] ?? null,
                $item['equipmentNotes'] ?? null,
            );
        }

        $this->createOrder->handle(new CreateOrderCommand(
            $orderId,
            (int) ($data['clientId'] ?? 0),
            (string) $data['estimatedAmount'],
            $items,
            $data['serviceType'],
            $data['billingType'],
            $data['urgency'],
            (bool) ($data['deliveryRequired'] ?? false),
            $data['defects'] ?? null,
            $data['internalNotes'] ?? null,
            'RUB',
            $data['newClientName'] ?? null,
            $data['newClientPhone'] ?? null,
            $data['newClientEmail'] ?? null,
            isset($data['warrantySourceOrderId']) ? (string) $data['warrantySourceOrderId'] : null,
        ));

        return $this->created($this->getOrderById->handle(new GetOrderByIdQuery($orderId)));
    }

    public function show(string $orderId): JsonResponse
    {
        $order = $this->getOrderById->handle(new GetOrderByIdQuery($orderId));

        if ($order === null) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return $this->ok($order);
    }

    public function completeReception(Request $request, string $orderId): JsonResponse
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

    public function cancel(Request $request, string $orderId): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $this->cancelOrder->handle(new CancelOrderCommand($orderId, $data['reason']));

        return $this->ok($this->getOrderById->handle(new GetOrderByIdQuery($orderId)));
    }

    public function close(string $orderId): JsonResponse
    {
        $this->closeOrder->handle(new CloseOrderCommand($orderId));

        return $this->ok($this->getOrderById->handle(new GetOrderByIdQuery($orderId)));
    }

    public function issue(string $orderId): JsonResponse
    {
        $this->issueOrder->handle(new IssueOrderCommand($orderId));

        return $this->ok($this->getOrderById->handle(new GetOrderByIdQuery($orderId)));
    }
}
