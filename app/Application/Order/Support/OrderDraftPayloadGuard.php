<?php

namespace App\Application\Order\Support;

use App\Domain\Crm\Repository\EquipmentRepository;
use App\Domain\Order\OrderItemKind;
use App\Shared\Domain\DomainException;

final readonly class OrderDraftPayloadGuard
{
    public function __construct(
        private EquipmentRepository $equipments,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function assertClientPayload(int $clientId, string $serviceType, array $payload): void
    {
        $items = $payload['items'] ?? null;
        if (! is_array($items) || $items === []) {
            throw new DomainException('payload.items must contain at least one item.');
        }

        foreach ($items as $item) {
            if (! is_array($item)) {
                throw new DomainException('Invalid draft item.');
            }
            $kind = (string) ($item['kind'] ?? '');
            if ($kind === OrderItemKind::Sharpening->value) {
                $title = trim((string) ($item['title'] ?? ''));
                $quantity = (int) ($item['quantity'] ?? 0);
                if ($title === '' || $quantity < 1) {
                    throw new DomainException('Sharpening draft item requires title and quantity.');
                }
                continue;
            }
            if ($kind === OrderItemKind::Repair->value) {
                $equipmentId = (int) ($item['equipment_id'] ?? 0);
                if ($equipmentId < 1) {
                    throw new DomainException('Repair draft item requires equipment_id.');
                }
                $equipment = $this->equipments->findById($equipmentId);
                if ($equipment === null || $equipment->isDeleted()) {
                    throw new DomainException('Equipment not found.');
                }
                if ($equipment->clientId() !== $clientId) {
                    throw new DomainException('Equipment does not belong to this client.');
                }
                continue;
            }
            throw new DomainException('Invalid draft item kind.');
        }

        if ($serviceType === 'sharpening' || $serviceType === 'repair') {
            return;
        }

        throw new DomainException('Invalid service_type.');
    }
}
