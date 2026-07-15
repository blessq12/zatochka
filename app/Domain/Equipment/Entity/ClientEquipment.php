<?php

namespace App\Domain\Equipment\Entity;

use App\Domain\Equipment\Event\ComponentAdded;
use App\Domain\Equipment\Event\EquipmentRegistered;
use App\Domain\Equipment\Event\SerialNumberRegistered;
use App\Domain\Equipment\VO\SerialNumber;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class ClientEquipment extends AggregateRoot
{
    /** @var array<int, EquipmentComponent> */
    private array $components = [];

    /** @var list<RepairHistoryEntry> */
    private array $repairHistory = [];

    private function __construct(
        private readonly EntityId $id,
        private readonly EntityId $clientId,
        private readonly string $title,
        private readonly ?string $notes = null,
    ) {
        if (trim($this->title) === '') {
            throw new DomainException('Equipment title cannot be empty.');
        }
    }

    public static function register(
        EntityId $id,
        EntityId $clientId,
        string $title,
        ?string $notes = null,
    ): self {
        $equipment = new self($id, $clientId, $title, $notes);
        $equipment->record(new EquipmentRegistered($id, $clientId, $title));

        return $equipment;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function clientId(): EntityId
    {
        return $this->clientId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    /** @return list<EquipmentComponent> */
    public function components(): array
    {
        return array_values($this->components);
    }

    /** @return list<RepairHistoryEntry> */
    public function repairHistory(): array
    {
        return $this->repairHistory;
    }

    public function addComponent(EquipmentComponent $component): void
    {
        if (isset($this->components[$component->id()->value])) {
            throw new DomainException('Component already exists on this equipment.');
        }

        $this->components[$component->id()->value] = $component;
        $this->record(new ComponentAdded($this->id, $component->id(), $component->name()));
    }

    public function registerComponentSerial(EntityId $componentId, SerialNumber $serialNumber): void
    {
        $component = $this->components[$componentId->value] ?? null;

        if ($component === null) {
            throw new DomainException('Component not found on this equipment.');
        }

        $component->registerSerialNumber($serialNumber);
        $this->record(new SerialNumberRegistered($this->id, $componentId, $serialNumber->value));
    }

    public function appendRepairHistory(RepairHistoryEntry $entry): void
    {
        $this->repairHistory[] = $entry;
    }
}
