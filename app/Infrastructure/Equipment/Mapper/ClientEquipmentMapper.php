<?php

namespace App\Infrastructure\Equipment\Mapper;

use App\Application\Equipment\DTO\ClientEquipmentDTO;
use App\Domain\Equipment\Entity\ClientEquipment;
use App\Domain\Equipment\Entity\EquipmentComponent;
use App\Domain\Equipment\Entity\RepairHistoryEntry;
use App\Domain\Equipment\VO\EquipmentType;
use App\Domain\Equipment\VO\SerialNumber;
use App\Infrastructure\Equipment\Model\ClientEquipmentModel;
use App\Infrastructure\Equipment\Model\EquipmentComponentModel;
use App\Infrastructure\Equipment\Model\RepairHistoryModel;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final class ClientEquipmentMapper
{
    public function toDomain(ClientEquipmentModel $model): ClientEquipment
    {
        $components = [];

        foreach ($model->components as $row) {
            $components[] = EquipmentComponent::reconstitute(
                new EntityId((int) $row->id),
                (string) $row->name,
                $row->serial_number !== null ? new SerialNumber((string) $row->serial_number) : null,
            );
        }

        $history = [];

        foreach ($model->repairHistory as $row) {
            $history[] = new RepairHistoryEntry(
                new EntityId((int) $row->id),
                new EntityId((int) $row->order_item_id),
                (string) $row->summary,
                DateTimeImmutable::createFromInterface($row->recorded_at),
            );
        }

        return ClientEquipment::reconstitute(
            new EntityId((int) $model->id),
            (string) $model->title,
            (string) $model->brand,
            (string) $model->model_name,
            EquipmentType::tryFrom((string) ($model->equipment_type ?? ''))
                ?? EquipmentType::Other,
            $model->client_id !== null ? new EntityId((int) $model->client_id) : null,
            $model->notes !== null ? (string) $model->notes : null,
            $components,
            $history,
        );
    }

    public function toPersistence(ClientEquipment $equipment, ?ClientEquipmentModel $model = null): ClientEquipmentModel
    {
        $model ??= new ClientEquipmentModel();
        $model->id = $equipment->id()->value;
        $model->client_id = $equipment->clientId()?->value;
        $model->title = $equipment->title();
        $model->brand = $equipment->brand();
        $model->model_name = $equipment->modelName();
        $model->equipment_type = $equipment->equipmentType()->value;
        $model->notes = $equipment->notes();

        return $model;
    }

    /** @return list<EquipmentComponentModel> */
    public function componentsToPersistence(ClientEquipment $equipment): array
    {
        $rows = [];

        foreach ($equipment->components() as $component) {
            $row = new EquipmentComponentModel();
            $row->id = $component->id()->value;
            $row->equipment_id = $equipment->id()->value;
            $row->name = $component->name();
            $row->serial_number = $component->serialNumber()?->value;
            $rows[] = $row;
        }

        return $rows;
    }

    /** @return list<RepairHistoryModel> */
    public function historyToPersistence(ClientEquipment $equipment): array
    {
        $rows = [];

        foreach ($equipment->repairHistory() as $entry) {
            $row = new RepairHistoryModel();
            $row->id = $entry->id->value;
            $row->equipment_id = $equipment->id()->value;
            $row->order_item_id = $entry->orderItemId->value;
            $row->summary = $entry->summary;
            $row->recorded_at = $entry->recordedAt;
            $rows[] = $row;
        }

        return $rows;
    }

    public function toDTO(ClientEquipmentModel $model): ClientEquipmentDTO
    {
        $components = [];

        foreach ($model->components as $row) {
            $components[] = [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'serialNumber' => $row->serial_number !== null ? (string) $row->serial_number : null,
            ];
        }

        $history = [];

        foreach ($model->repairHistory as $row) {
            $history[] = [
                'id' => (int) $row->id,
                'orderItemId' => (int) $row->order_item_id,
                'summary' => (string) $row->summary,
                'recordedAt' => $row->recorded_at->toIso8601String(),
            ];
        }

        return new ClientEquipmentDTO(
            (int) $model->id,
            $model->client_id !== null ? (int) $model->client_id : null,
            (string) $model->title,
            (string) $model->brand,
            (string) $model->model_name,
            (string) ($model->equipment_type ?: EquipmentType::Other->value),
            $model->notes !== null ? (string) $model->notes : null,
            $components,
            $history,
        );
    }
}
