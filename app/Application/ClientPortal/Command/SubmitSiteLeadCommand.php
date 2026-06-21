<?php

namespace App\Application\ClientPortal\Command;

final readonly class SubmitSiteLeadCommand
{
    /**
     * @param  list<string>  $serviceTypes
     */
    public function __construct(
        public string $fullName,
        public string $phone,
        public array $serviceTypes,
        public ?string $email = null,
        public ?string $comment = null,
        public ?array $intakeData = null,
        public bool $needsDelivery = false,
        public ?string $deliveryAddress = null,
    ) {}
}
