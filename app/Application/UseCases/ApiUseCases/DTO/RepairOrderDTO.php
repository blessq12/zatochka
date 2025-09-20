<?php

namespace App\Application\UseCases\ApiUseCases\DTO;

class RepairOrderDTO extends BaseOrderCreateDTO
{
    public function __construct(
        string $serviceType,
        string $clientName,
        string $clientPhone,
        bool $agreement,
        bool $privacyAgreement,
        public readonly string $equipmentType,
        public readonly string $equipmentName,
        ?string $problemDescription = null,
        \App\Domain\Order\Enum\OrderUrgency $urgency = \App\Domain\Order\Enum\OrderUrgency::NORMAL,
        bool $needsDelivery = false,
        ?string $deliveryAddress = null,
    ) {
        parent::__construct(
            serviceType: $serviceType,
            clientName: $clientName,
            clientPhone: $clientPhone,
            agreement: $agreement,
            privacyAgreement: $privacyAgreement,
            problemDescription: $problemDescription,
            urgency: $urgency,
            needsDelivery: $needsDelivery,
            deliveryAddress: $deliveryAddress,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            serviceType: $data['service_type'],
            clientName: $data['client_name'],
            clientPhone: $data['client_phone'],
            agreement: $data['agreement'],
            privacyAgreement: $data['privacy_agreement'],
            equipmentType: $data['equipment_type'],
            equipmentName: $data['equipment_name'],
            problemDescription: $data['problem_description'] ?? null,
            urgency: isset($data['urgency']) ? \App\Domain\Order\Enum\OrderUrgency::from($data['urgency']) : \App\Domain\Order\Enum\OrderUrgency::NORMAL,
            needsDelivery: $data['needs_delivery'] ?? false,
            deliveryAddress: $data['delivery_address'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'equipment_type' => $this->equipmentType,
            'equipment_name' => $this->equipmentName,
        ]);
    }
}
