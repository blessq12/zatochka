<?php

namespace App\Http\Controllers\Order;

use App\Application\Order\Command\CancelOrderCommand;
use App\Application\Order\Command\CancelOrderHandler;
use App\Application\Order\Command\CloseOrderCommand;
use App\Application\Order\Command\CloseOrderHandler;
use App\Application\Order\Command\CreateOrderCommand;
use App\Application\Order\Command\CreateOrderHandler;
use App\Application\Order\Command\IssueOrderCommand;
use App\Application\Order\Command\IssueOrderHandler;
use App\Application\Order\Command\RejectOrderItemUnitsCommand;
use App\Application\Order\Command\RejectOrderItemUnitsHandler;
use App\Application\Order\DTO\CreateOrderItemDTO;
use App\Application\Order\Query\GetOrderByIdHandler;
use App\Application\Order\Query\GetOrderByIdQuery;
use App\Application\Order\ReadPort\OrderContainerReadPort;
use App\Domain\Finance\VO\PaymentMethod;
use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderSource;
use App\Domain\Order\VO\OrderUrgency;
use App\Domain\Order\VO\SharpeningToolType;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class OrderController extends Controller
{
    public function __construct(
        private CreateOrderHandler $createOrder,
        private CancelOrderHandler $cancelOrder,
        private CloseOrderHandler $closeOrder,
        private IssueOrderHandler $issueOrder,
        private RejectOrderItemUnitsHandler $rejectOrderItemUnits,
        private GetOrderByIdHandler $getOrderById,
        private OrderContainerReadPort $orderContainer,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'clientId' => ['nullable', 'integer', 'min:1'],
            'estimatedAmount' => ['required', 'numeric'],
            'serviceType' => ['required', 'string', Rule::enum(OrderServiceType::class)],
            'billingType' => ['required', 'string', Rule::enum(OrderBillingType::class)],
            'urgency' => ['required', 'string', Rule::enum(OrderUrgency::class)],
            'warrantySourceOrderId' => ['nullable', 'string', 'size:32'],
            'deliveryRequired' => ['nullable', 'boolean'],
            'defects' => ['nullable', 'string'],
            'internalNotes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.clientEquipmentId' => ['nullable', 'integer', 'min:1'],
            'items.*.toolName' => ['nullable', 'string'],
            'items.*.toolType' => ['nullable', 'string', Rule::enum(SharpeningToolType::class)],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $orderId = OrderId::generate()->value;
        $items = [];

        foreach ($data['items'] as $item) {
            $items[] = new CreateOrderItemDTO(
                null,
                isset($item['clientEquipmentId']) ? (int) $item['clientEquipmentId'] : null,
                $item['toolName'] ?? null,
                $item['toolType'] ?? null,
                isset($item['quantity']) ? (int) $item['quantity'] : null,
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
            isset($data['warrantySourceOrderId']) ? (string) $data['warrantySourceOrderId'] : null,
            null,
            OrderSource::Api->value,
        ));

        return $this->created($this->getOrderById->handle(new GetOrderByIdQuery($orderId)));
    }

    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $query = \App\Infrastructure\Order\Model\OrderModel::query()
            ->with(['client'])
            ->orderByDesc('created_at');

        if (is_string($status) && $status !== '') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($cq) use ($search): void {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $items = $query->limit(200)->get()->map(function ($order): array {
            $client = $order->client;

            return [
                'id' => $order->id,
                'number' => $order->number,
                'status' => $order->status,
                'serviceType' => $order->service_type,
                'urgency' => $order->urgency,
                'estimatedAmount' => $order->estimated_amount,
                'estimatedCurrency' => $order->estimated_currency,
                'createdAt' => $order->created_at?->toIso8601String(),
                'client' => $client === null ? null : [
                    'id' => $client->id,
                    'name' => $client->name,
                    'phone' => $client->phone,
                ],
            ];
        });

        return $this->ok(['items' => $items]);
    }

    public function show(string $orderId): JsonResponse
    {
        $order = $this->getOrderById->handle(new GetOrderByIdQuery($orderId));

        if ($order === null) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return $this->ok($order);
    }

    public function container(string $orderId): JsonResponse
    {
        $container = $this->orderContainer->findById($orderId);

        if ($container === null) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return $this->ok($container);
    }

    public function rejectItemUnits(Request $request, string $orderId, int $orderItemId): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
            'reason' => ['required', 'string'],
        ]);

        $this->rejectOrderItemUnits->handle(new RejectOrderItemUnitsCommand(
            $orderId,
            $orderItemId,
            (int) ($data['quantity'] ?? 1),
            $data['reason'],
        ));

        return $this->ok($this->orderContainer->findById($orderId));
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

    public function issue(Request $request, string $orderId): JsonResponse
    {
        $data = $request->validate([
            'paymentMethod' => ['nullable', 'string', Rule::enum(PaymentMethod::class)],
        ]);

        $this->issueOrder->handle(new IssueOrderCommand(
            $orderId,
            $data['paymentMethod'] ?? null,
        ));

        return $this->ok($this->getOrderById->handle(new GetOrderByIdQuery($orderId)));
    }
}
