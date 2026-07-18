<?php

namespace App\Application\CRM\Command;

final readonly class SubmitClientLeadCommand
{
    /**
     * @param list<string> $serviceTypes
     * @param array<string, mixed>|null $intakeData
     */
    public function __construct(
        public string $fullName,
        public string $phone,
        public ?string $email,
        public array $serviceTypes,
        public ?string $comment,
        public ?array $intakeData,
        public bool $needsDelivery,
        public ?string $deliveryAddress,
    ) {}
}
