<?php

namespace App\Http\Controllers\Delivery;

use App\Application\Delivery\Command\AssignCourierCommand;
use App\Application\Delivery\Command\AssignCourierHandler;
use App\Application\Delivery\Command\MarkEquipmentCollectedCommand;
use App\Application\Delivery\Command\MarkEquipmentCollectedHandler;
use App\Application\Delivery\Command\MarkOrderDeliveredCommand;
use App\Application\Delivery\Command\MarkOrderDeliveredHandler;
use App\Application\Delivery\Command\RequestDeliveryCommand;
use App\Application\Delivery\Command\RequestDeliveryHandler;
use App\Application\Delivery\Query\GetDeliveryRequestByIdHandler;
use App\Application\Delivery\Query\GetDeliveryRequestByIdQuery;
use App\Http\Controllers\Controller;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DeliveryRequestController extends Controller
{
    public function __construct(
        private RequestDeliveryHandler $requestDelivery,
        private AssignCourierHandler $assignCourier,
        private MarkEquipmentCollectedHandler $markCollected,
        private MarkOrderDeliveredHandler $markDelivered,
        private GetDeliveryRequestByIdHandler $getDeliveryRequestById,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'orderId' => ['required', 'string', 'size:32'],
            'city' => ['required', 'string'],
            'street' => ['required', 'string'],
            'building' => ['required', 'string'],
            'apartment' => ['nullable', 'string'],
            'comment' => ['nullable', 'string'],
            'pickup' => ['nullable', 'boolean'],
        ]);

        $deliveryRequestId = $this->ids->next('delivery_request')->value;

        $this->requestDelivery->handle(new RequestDeliveryCommand(
            $deliveryRequestId,
            (string) $data['orderId'],
            $data['city'],
            $data['street'],
            $data['building'],
            $data['apartment'] ?? null,
            $data['comment'] ?? null,
            (bool) ($data['pickup'] ?? false),
        ));

        return $this->created(
            $this->getDeliveryRequestById->handle(new GetDeliveryRequestByIdQuery($deliveryRequestId))
        );
    }

    public function show(int $deliveryRequestId): JsonResponse
    {
        $request = $this->getDeliveryRequestById->handle(new GetDeliveryRequestByIdQuery($deliveryRequestId));

        if ($request === null) {
            return response()->json(['message' => 'Delivery request not found.'], 404);
        }

        return $this->ok($request);
    }

    public function assignCourier(Request $request, int $deliveryRequestId): JsonResponse
    {
        $data = $request->validate([
            'courierId' => ['required', 'integer'],
        ]);

        $this->assignCourier->handle(new AssignCourierCommand(
            $deliveryRequestId,
            (int) $data['courierId'],
        ));

        return $this->ok(
            $this->getDeliveryRequestById->handle(new GetDeliveryRequestByIdQuery($deliveryRequestId))
        );
    }

    public function collect(int $deliveryRequestId): JsonResponse
    {
        $this->markCollected->handle(new MarkEquipmentCollectedCommand($deliveryRequestId));

        return $this->ok(
            $this->getDeliveryRequestById->handle(new GetDeliveryRequestByIdQuery($deliveryRequestId))
        );
    }

    public function deliver(int $deliveryRequestId): JsonResponse
    {
        $this->markDelivered->handle(new MarkOrderDeliveredCommand($deliveryRequestId));

        return $this->ok(
            $this->getDeliveryRequestById->handle(new GetDeliveryRequestByIdQuery($deliveryRequestId))
        );
    }
}
