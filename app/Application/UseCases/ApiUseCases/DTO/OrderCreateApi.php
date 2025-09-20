<?php

namespace App\Application\UseCases\ApiUseCases\DTO;

class OrderCreateApi
{
    public function __construct(
        public BaseOrderCreateDTO $orderData,
    ) {}

    public static function fromArray(array $data): self
    {
        $serviceType = $data['service_type'];

        $orderData = match ($serviceType) {
            'repair' => RepairOrderDTO::fromArray($data),
            'sharpening' => SharpeningOrderDTO::fromArray($data),
            default => throw new \InvalidArgumentException("Неподдерживаемый тип услуги: {$serviceType}")
        };

        return new self(orderData: $orderData);
    }

    public function toArray(): array
    {
        return $this->orderData->toArray();
    }
}
