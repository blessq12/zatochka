<?php

namespace App\Application\Order\Command;

final readonly class CreatePublicOrderCommand
{
    /**
     * @param array<string, mixed>|null $intake
     */
    public function __construct(
        public string $fullName,
        public string $phone,
        public string $serviceType,
        public bool $needsDelivery = false,
        public ?string $deliveryAddress = null,
        public ?string $comment = null,
        public ?array $intake = null,
        public ?int $authenticatedClientId = null,
    ) {}
}
