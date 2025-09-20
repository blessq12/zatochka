<?php

namespace App\Application\UseCases\ApiUseCases\DTO;

class SharpeningOrderDTO extends BaseOrderCreateDTO
{
    public function __construct(
        string $serviceType,
        string $clientName,
        string $clientPhone,
        bool $agreement,
        bool $privacyAgreement,
        public readonly string $toolType,
        public readonly int $totalToolsCount,
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
            toolType: $data['tool_type'],
            totalToolsCount: $data['total_tools_count'],
            problemDescription: $data['problem_description'] ?? null,
            urgency: isset($data['urgency']) ? \App\Domain\Order\Enum\OrderUrgency::from($data['urgency']) : \App\Domain\Order\Enum\OrderUrgency::NORMAL,
            needsDelivery: $data['needs_delivery'] ?? false,
            deliveryAddress: $data['delivery_address'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'tool_type' => $this->toolType,
            'total_tools_count' => $this->totalToolsCount,
        ]);
    }
}
