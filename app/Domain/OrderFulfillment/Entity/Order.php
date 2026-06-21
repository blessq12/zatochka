<?php

namespace App\Domain\OrderFulfillment\Entity;

use App\Domain\OrderFulfillment\Enum\OrderSource;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Domain\OrderFulfillment\Enum\OrderUrgency;
use App\Domain\OrderFulfillment\Exception\OrderPolicyViolation;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use App\Domain\OrderFulfillment\ValueObject\OrderNumber;
use DateTimeImmutable;

final class Order
{
    /**
     * @param  list<string>  $serviceTypes
     * @param  list<OrderWork>  $works
     * @param  list<OrderTool>  $tools
     * @param  list<OrderMaterial>  $materials
     */
    public function __construct(
        private ?int $id,
        private OrderNumber $orderNumber,
        private OrderStatus $status,
        private array $serviceTypes,
        private ?OrderUrgency $urgency,
        private bool $isWarranty,
        private bool $needsDelivery,
        private ?string $deliveryAddress,
        private ?string $problemDescription,
        private ?string $internalNotes,
        private ?string $price,
        private OrderSource $source,
        private ?ClientSnapshot $clientSnapshot,
        private ?int $leadId,
        private ?int $clientId,
        private ?int $equipmentId,
        private ?int $masterId,
        private ?int $managerId,
        private int $branchId,
        private ?int $warrantyParentOrderId,
        private ?DateTimeImmutable $takenAt,
        private ?DateTimeImmutable $readyAt,
        private ?DateTimeImmutable $issuedAt,
        private ?DateTimeImmutable $createdAt = null,
        private array $works = [],
        private array $tools = [],
        private array $materials = [],
    ) {
        self::assertClientSnapshotWhenGuest($clientId, $clientSnapshot);
    }

    /**
     * @param  list<string>  $serviceTypes
     * @param  list<OrderTool>  $tools
     */
    public static function create(
        OrderNumber $orderNumber,
        array $serviceTypes,
        OrderSource $source,
        int $branchId,
        ?int $clientId = null,
        ?ClientSnapshot $clientSnapshot = null,
        ?int $leadId = null,
        ?OrderUrgency $urgency = null,
        bool $isWarranty = false,
        bool $needsDelivery = false,
        ?string $deliveryAddress = null,
        ?string $problemDescription = null,
        ?int $equipmentId = null,
        ?int $warrantyParentOrderId = null,
        ?int $masterId = null,
        ?int $managerId = null,
        array $tools = [],
    ): self {
        return new self(
            id: null,
            orderNumber: $orderNumber,
            status: OrderStatus::New,
            serviceTypes: $serviceTypes,
            urgency: $urgency ?? OrderUrgency::Standard,
            isWarranty: $isWarranty,
            needsDelivery: $needsDelivery,
            deliveryAddress: $deliveryAddress,
            problemDescription: $problemDescription,
            internalNotes: null,
            price: null,
            source: $source,
            clientSnapshot: $clientSnapshot,
            leadId: $leadId,
            clientId: $clientId,
            equipmentId: $equipmentId,
            masterId: $masterId,
            managerId: $managerId,
            branchId: $branchId,
            warrantyParentOrderId: $warrantyParentOrderId,
            takenAt: null,
            readyAt: null,
            issuedAt: null,
            createdAt: null,
            works: [],
            tools: $tools,
            materials: [],
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function orderNumber(): OrderNumber
    {
        return $this->orderNumber;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    /** @return list<string> */
    public function serviceTypes(): array
    {
        return $this->serviceTypes;
    }

    public function urgency(): ?OrderUrgency
    {
        return $this->urgency;
    }

    public function isWarranty(): bool
    {
        return $this->isWarranty;
    }

    public function needsDelivery(): bool
    {
        return $this->needsDelivery;
    }

    public function deliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function problemDescription(): ?string
    {
        return $this->problemDescription;
    }

    public function internalNotes(): ?string
    {
        return $this->internalNotes;
    }

    public function price(): ?string
    {
        return $this->price;
    }

    public function source(): OrderSource
    {
        return $this->source;
    }

    public function clientSnapshot(): ?ClientSnapshot
    {
        return $this->clientSnapshot;
    }

    public function leadId(): ?int
    {
        return $this->leadId;
    }

    public function clientId(): ?int
    {
        return $this->clientId;
    }

    public function equipmentId(): ?int
    {
        return $this->equipmentId;
    }

    public function masterId(): ?int
    {
        return $this->masterId;
    }

    public function managerId(): ?int
    {
        return $this->managerId;
    }

    public function branchId(): int
    {
        return $this->branchId;
    }

    public function warrantyParentOrderId(): ?int
    {
        return $this->warrantyParentOrderId;
    }

    public function takenAt(): ?DateTimeImmutable
    {
        return $this->takenAt;
    }

    public function readyAt(): ?DateTimeImmutable
    {
        return $this->readyAt;
    }

    public function issuedAt(): ?DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return list<OrderWork> */
    public function works(): array
    {
        return $this->works;
    }

    /** @return list<OrderTool> */
    public function tools(): array
    {
        return $this->tools;
    }

    /** @return list<OrderMaterial> */
    public function materials(): array
    {
        return $this->materials;
    }

    public function clientDisplayName(): string
    {
        return $this->clientSnapshot?->fullName() ?? '';
    }

    public function clientDisplayPhone(): string
    {
        return $this->clientSnapshot?->phone() ?? '';
    }

    public function isActive(): bool
    {
        return ! in_array($this->status, [OrderStatus::Issued, OrderStatus::Cancelled], true);
    }

    public function assignId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    /** POL-08: только из new. */
    public function assignMaster(int $masterId): self
    {
        $this->assertStatus(OrderStatus::New);

        $clone = clone $this;
        $clone->masterId = $masterId;

        return $clone;
    }

    /** POL-06: new → in_work, мастер совпадает с назначенным. */
    public function takeToWork(int $actingMasterId, DateTimeImmutable $at): self
    {
        $this->assertStatus(OrderStatus::New);

        if ($this->masterId === null) {
            throw new OrderPolicyViolation('Нельзя взять заказ в работу без назначенного мастера.');
        }

        if ($this->masterId !== $actingMasterId) {
            throw new OrderPolicyViolation('Заказ назначен другому мастеру.');
        }

        $clone = clone $this;
        $clone->status = OrderStatus::InWork;
        $clone->takenAt = $at;

        return $clone;
    }

    public function markWaitingForParts(): self
    {
        $this->assertStatus(OrderStatus::InWork);

        $clone = clone $this;
        $clone->status = OrderStatus::WaitingParts;

        return $clone;
    }

    public function resume(): self
    {
        $this->assertStatus(OrderStatus::WaitingParts);

        $clone = clone $this;
        $clone->status = OrderStatus::InWork;

        return $clone;
    }

    /** POL-01: ≥1 работа. */
    public function markReady(DateTimeImmutable $at): self
    {
        $this->assertStatus(OrderStatus::InWork);

        if ($this->works === []) {
            throw new OrderPolicyViolation('Нельзя завершить заказ без хотя бы одной работы.');
        }

        $clone = clone $this;
        $clone->status = OrderStatus::Ready;
        $clone->readyAt = $at;

        return $clone;
    }

    public function returnToWork(): self
    {
        $this->assertStatus(OrderStatus::Ready);

        $clone = clone $this;
        $clone->status = OrderStatus::InWork;
        $clone->readyAt = null;

        return $clone;
    }

    /** POL-02: только из ready. */
    public function issue(DateTimeImmutable $at): self
    {
        $this->assertStatus(OrderStatus::Ready);

        $clone = clone $this;
        $clone->status = OrderStatus::Issued;
        $clone->issuedAt = $at;

        return $clone;
    }

    /** POL-03: только из new. */
    public function cancel(): self
    {
        $this->assertStatus(OrderStatus::New);

        $clone = clone $this;
        $clone->status = OrderStatus::Cancelled;

        return $clone;
    }

    public function updateInternalNotes(?string $notes): self
    {
        $this->assertMasterWorkspace();

        $clone = clone $this;
        $clone->internalNotes = $notes;

        return $clone;
    }

    public function addWork(OrderWork $work): self
    {
        $this->assertMasterWorkspace();

        $clone = clone $this;
        $clone->works = [...$this->works, $work];

        return $clone;
    }

    public function removeWork(int $sortOrder): self
    {
        $this->assertMasterWorkspace();

        $filtered = array_values(array_filter(
            $this->works,
            static fn (OrderWork $work): bool => $work->sortOrder !== $sortOrder,
        ));

        if (count($filtered) === count($this->works)) {
            throw new OrderPolicyViolation('Работа не найдена в заказе.');
        }

        $clone = clone $this;
        $clone->works = $filtered;

        return $clone;
    }

    public function setWorkPrice(int $sortOrder, ?string $price): self
    {
        $this->assertNotFinal();

        $found = false;
        $works = [];

        foreach ($this->works as $work) {
            if ($work->sortOrder === $sortOrder) {
                $found = true;
                $works[] = new OrderWork($work->id, $work->description, $price, $work->sortOrder);
            } else {
                $works[] = $work;
            }
        }

        if (! $found) {
            throw new OrderPolicyViolation('Работа не найдена в заказе.');
        }

        $clone = clone $this;
        $clone->works = $works;

        return $clone;
    }

    public function nextWorkSortOrder(): int
    {
        if ($this->works === []) {
            return 0;
        }

        return max(array_map(static fn (OrderWork $work): int => $work->sortOrder, $this->works)) + 1;
    }

    public function addMaterial(OrderMaterial $material): self
    {
        $this->assertNotFinal();

        $clone = clone $this;
        $clone->materials = [...$this->materials, $material];

        return $clone;
    }

    public function removeMaterial(int $materialId): self
    {
        $this->assertNotFinal();

        $filtered = array_values(array_filter(
            $this->materials,
            static fn (OrderMaterial $material): bool => $material->id !== $materialId,
        ));

        if (count($filtered) === count($this->materials)) {
            throw new OrderPolicyViolation('Материал не найден в заказе.');
        }

        $clone = clone $this;
        $clone->materials = $filtered;

        return $clone;
    }

    /** POL-10: без авто-проверки parent. */
    public function linkWarranty(?int $parentOrderId): self
    {
        $this->assertNotFinal();

        $clone = clone $this;
        $clone->isWarranty = true;
        $clone->warrantyParentOrderId = $parentOrderId;

        return $clone;
    }

    public function linkEquipment(int $equipmentId): self
    {
        $this->assertNotFinal();

        $clone = clone $this;
        $clone->equipmentId = $equipmentId;

        return $clone;
    }

    /** POL-05: вызывается только явно из Application. */
    public function recalculatePrice(): self
    {
        $this->assertNotFinal();

        $total = '0.00';

        foreach ($this->works as $work) {
            if ($work->price !== null) {
                $total = bcadd($total, $work->price, 2);
            }
        }

        foreach ($this->materials as $material) {
            $total = bcadd($total, $material->totalPrice, 2);
        }

        $clone = clone $this;
        $clone->price = bccomp($total, '0', 2) === 0 ? null : $total;

        return $clone;
    }

    private function assertStatus(OrderStatus $expected): void
    {
        if ($this->status !== $expected) {
            throw new OrderPolicyViolation(sprintf(
                'Недопустимый статус заказа: ожидался «%s», текущий — «%s».',
                $expected->label(),
                $this->status->label(),
            ));
        }
    }

    private function assertNotFinal(): void
    {
        if (in_array($this->status, [OrderStatus::Issued, OrderStatus::Cancelled], true)) {
            throw new OrderPolicyViolation('Заказ в финальном статусе и не может быть изменён.');
        }
    }

    /** POL: мастер редактирует состав заказа только в статусе «в работе». */
    private function assertMasterWorkspace(): void
    {
        if ($this->status !== OrderStatus::InWork) {
            throw new OrderPolicyViolation('Заказ нельзя изменить в текущем статусе.');
        }
    }

    private static function assertClientSnapshotWhenGuest(?int $clientId, ?ClientSnapshot $clientSnapshot): void
    {
        if ($clientId !== null) {
            return;
        }

        if ($clientSnapshot === null) {
            throw new OrderPolicyViolation('Для гостевого заказа обязателен снимок клиента.');
        }

        if (trim($clientSnapshot->fullName()) === '' || trim($clientSnapshot->phone()) === '') {
            throw new OrderPolicyViolation('Снимок клиента должен содержать имя и телефон.');
        }
    }
}
