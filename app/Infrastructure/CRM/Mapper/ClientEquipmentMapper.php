<?php

namespace App\Infrastructure\CRM\Mapper;

use App\Application\CRM\DTO\ClientEquipmentDTO;
use App\Domain\CRM\Entity\ClientEquipment;
use App\Domain\CRM\Entity\EquipmentComponent;
use App\Domain\CRM\VO\EquipmentNumber;
use App\Domain\CRM\VO\EquipmentType;
use App\Domain\CRM\VO\SerialNumber;
use App\Infrastructure\CRM\Model\ClientEquipmentModel;
use App\Infrastructure\CRM\Model\EquipmentComponentModel;
use App\Shared\ValueObject\EntityId;

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

        return ClientEquipment::reconstitute(
            new EntityId((int) $model->id),
            new EquipmentNumber((string) $model->number),
            (string) $model->title,
            (string) $model->brand,
            (string) $model->model_name,
            EquipmentType::tryFrom((string) ($model->equipment_type ?? ''))
                ?? EquipmentType::Other,
            $model->client_id !== null ? new EntityId((int) $model->client_id) : null,
            $components,
        );
    }

    public function toPersistence(ClientEquipment $equipment, ?ClientEquipmentModel $model = null): ClientEquipmentModel
    {
        $model ??= new ClientEquipmentModel();
        $model->id = $equipment->id()->value;
        $model->number = $equipment->number()->value;
        $model->client_id = $equipment->clientId()?->value;
        $model->title = $equipment->title();
        $model->brand = $equipment->brand();
        $model->model_name = $equipment->modelName();
        $model->equipment_type = $equipment->equipmentType()->value;

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

        return new ClientEquipmentDTO(
            (int) $model->id,
            (string) $model->number,
            $model->client_id !== null ? (int) $model->client_id : null,
            (string) $model->title,
            (string) $model->brand,
            (string) $model->model_name,
            (string) ($model->equipment_type ?: EquipmentType::Other->value),
            $components,
        );
    }
}
