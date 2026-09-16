<?php

namespace App\Domain\CRM\Entity;

use App\Domain\CRM\Event\ComponentAdded;
use App\Domain\CRM\Event\EquipmentRegistered;
use App\Domain\CRM\Event\SerialNumberRegistered;
use App\Domain\CRM\VO\EquipmentNumber;
use App\Domain\CRM\VO\EquipmentType;
use App\Domain\CRM\VO\SerialNumber;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class ClientEquipment extends AggregateRoot
{
    /** @var array<int, EquipmentComponent> */
    private array $components = [];

    private function __construct(
        private readonly EntityId $id,
        private readonly EquipmentNumber $number,
        private ?EntityId $clientId,
        private string $title,
        private string $brand,
        private string $modelName,
        private EquipmentType $equipmentType,
    ) {
        $this->assertNonEmptyLabel($this->title, 'Equipment title cannot be empty.');
        $this->assertNonEmptyLabel($this->brand, 'Equipment brand cannot be empty.');
        $this->assertNonEmptyLabel($this->modelName, 'Equipment model cannot be empty.');
    }

    public static function register(
        EntityId $id,
        EquipmentNumber $number,
        string $title,
        string $brand,
        string $modelName,
        EquipmentType $equipmentType,
        ?EntityId $clientId = null,
    ): self {
        $equipment = new self($id, $number, $clientId, $title, $brand, $modelName, $equipmentType);
        $equipment->record(new EquipmentRegistered($id, $title, $clientId));

        return $equipment;
    }

    /**
     * @param list<EquipmentComponent> $components
     */
    public static function reconstitute(
        EntityId $id,
        EquipmentNumber $number,
        string $title,
        string $brand,
        string $modelName,
        EquipmentType $equipmentType,
        ?EntityId $clientId = null,
        array $components = [],
    ): self {
        $equipment = new self($id, $number, $clientId, $title, $brand, $modelName, $equipmentType);

        foreach ($components as $component) {
            $equipment->components[$component->id()->value] = $component;
        }

        return $equipment;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function number(): EquipmentNumber
    {
        return $this->number;
    }

    public function clientId(): ?EntityId
    {
        return $this->clientId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function brand(): string
    {
        return $this->brand;
    }

    public function modelName(): string
    {
        return $this->modelName;
    }

    public function equipmentType(): EquipmentType
    {
        return $this->equipmentType;
    }

    /** @return list<EquipmentComponent> */
    public function components(): array
    {
        return array_values($this->components);
    }

    public function findComponent(EntityId $componentId): ?EquipmentComponent
    {
        return $this->components[$componentId->value] ?? null;
    }

    public function addComponent(EquipmentComponent $component, ?SerialNumber $serialNumber = null): void
    {
        if (isset($this->components[$component->id()->value])) {
            throw new DomainException('Component already exists on this equipment.');
        }

        if ($serialNumber !== null) {
            $component->registerSerialNumber($serialNumber);
        }

        $this->components[$component->id()->value] = $component;
        $this->record(new ComponentAdded($this->id, $component->id(), $component->name()));

        if ($serialNumber !== null) {
            $this->record(new SerialNumberRegistered($this->id, $component->id(), $serialNumber->value));
        }
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

    public function updateProfile(
        string $title,
        string $brand,
        string $modelName,
        EquipmentType $equipmentType,
        ?EntityId $clientId = null,
    ): void {
        $this->assertNonEmptyLabel($title, 'Equipment title cannot be empty.');
        $this->assertNonEmptyLabel($brand, 'Equipment brand cannot be empty.');
        $this->assertNonEmptyLabel($modelName, 'Equipment model cannot be empty.');

        $this->title = $title;
        $this->brand = $brand;
        $this->modelName = $modelName;
        $this->equipmentType = $equipmentType;
        $this->clientId = $clientId;
    }

    private function assertNonEmptyLabel(string $value, string $message): void
    {
        if (trim($value) === '') {
            throw new DomainException($message);
        }
    }
}
