<?php

namespace App\Domain\SiteContent\Entity;

use App\Shared\Domain\DomainException;

final readonly class DeliveryAdvantage
{
    public function __construct(
        public string $title,
        public string $description,
    ) {
        if (trim($title) === '') {
            throw new DomainException('Delivery advantage title is required.');
        }

        if (trim($description) === '') {
            throw new DomainException('Delivery advantage description is required.');
        }
    }

    /** @return array{title: string, description: string} */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
