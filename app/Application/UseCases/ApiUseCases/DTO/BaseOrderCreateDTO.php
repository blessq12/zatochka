<?php

namespace App\Application\UseCases\ApiUseCases\DTO;

use App\Domain\Order\Enum\OrderUrgency;

abstract class BaseOrderCreateDTO
{
    public function __construct(
        public readonly string $serviceType,
        public readonly string $clientName,
        public readonly string $clientPhone,
        public readonly bool $agreement,
        public readonly bool $privacyAgreement,
        public readonly ?string $problemDescription = null,
        public readonly OrderUrgency $urgency = OrderUrgency::NORMAL,
        public readonly bool $needsDelivery = false,
        public readonly ?string $deliveryAddress = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            serviceType: $data['service_type'],
            clientName: $data['client_name'],
            clientPhone: $data['client_phone'],
            agreement: $data['agreement'],
            privacyAgreement: $data['privacy_agreement'],
            problemDescription: $data['problem_description'] ?? null,
            urgency: isset($data['urgency']) ? OrderUrgency::from($data['urgency']) : OrderUrgency::NORMAL,
            needsDelivery: $data['needs_delivery'] ?? false,
            deliveryAddress: $data['delivery_address'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'service_type' => $this->serviceType,
            'client_name' => $this->clientName,
            'client_phone' => $this->clientPhone,
            'agreement' => $this->agreement,
            'privacy_agreement' => $this->privacyAgreement,
            'problem_description' => $this->problemDescription,
            'urgency' => $this->urgency->value,
            'needs_delivery' => $this->needsDelivery,
            'delivery_address' => $this->deliveryAddress,
        ];
    }
}
