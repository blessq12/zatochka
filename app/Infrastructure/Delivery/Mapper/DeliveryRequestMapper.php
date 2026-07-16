<?php

namespace App\Infrastructure\Delivery\Mapper;

use App\Application\Delivery\DTO\DeliveryRequestDTO;
use App\Domain\Delivery\Entity\CourierAssignment;
use App\Domain\Delivery\Entity\DeliveryRequest;
use App\Domain\Delivery\VO\DeliveryAddress;
use App\Domain\Delivery\VO\DeliveryStatus;
use App\Infrastructure\Delivery\Model\DeliveryRequestModel;
use App\Domain\Order\VO\OrderId;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final class DeliveryRequestMapper
{
    public function toDomain(DeliveryRequestModel $model): DeliveryRequest
    {
        $courier = null;

        if ($model->courier_id !== null) {
            $courier = new CourierAssignment(
                new EntityId((int) $model->courier_id),
                $model->courier_assigned_at !== null
                    ? DateTimeImmutable::createFromInterface($model->courier_assigned_at)
                    : new DateTimeImmutable(),
            );
        }

        return DeliveryRequest::reconstitute(
            new EntityId((int) $model->id),
            new OrderId((string) $model->order_id),
            new DeliveryAddress(
                (string) $model->city,
                (string) $model->street,
                (string) $model->building,
                $model->apartment !== null ? (string) $model->apartment : null,
                $model->comment !== null ? (string) $model->comment : null,
            ),
            (bool) $model->pickup,
            DeliveryStatus::from((string) $model->status),
            $courier,
        );
    }

    public function toPersistence(DeliveryRequest $request, ?DeliveryRequestModel $model = null): DeliveryRequestModel
    {
        $model ??= new DeliveryRequestModel();
        $address = $request->address();
        $courier = $request->courierAssignment();

        $model->id = $request->id()->value;
        $model->order_id = $request->orderId()->value;
        $model->status = $request->status()->value;
        $model->pickup = $request->isPickup();
        $model->city = $address->city;
        $model->street = $address->street;
        $model->building = $address->building;
        $model->apartment = $address->apartment;
        $model->comment = $address->comment;
        $model->courier_id = $courier?->courierId->value;
        $model->courier_assigned_at = $courier?->assignedAt;

        return $model;
    }

    public function toDTO(DeliveryRequestModel $model): DeliveryRequestDTO
    {
        return new DeliveryRequestDTO(
            (int) $model->id,
            (string) $model->order_id,
            (string) $model->status,
            (bool) $model->pickup,
            (string) $model->city,
            (string) $model->street,
            (string) $model->building,
            $model->apartment !== null ? (string) $model->apartment : null,
            $model->courier_id !== null ? (int) $model->courier_id : null,
        );
    }
}
