<?php

namespace App\Domain\Delivery\VO;

use App\Shared\Domain\DomainException;

final readonly class DeliveryAddress
{
    public function __construct(
        public string $city,
        public string $street,
        public string $building,
        public ?string $apartment = null,
        public ?string $comment = null,
    ) {
        if (trim($this->city) === '' || trim($this->street) === '' || trim($this->building) === '') {
            throw new DomainException('City, street and building are required for delivery address.');
        }
    }
}
