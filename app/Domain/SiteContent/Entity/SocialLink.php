<?php

namespace App\Domain\SiteContent\Entity;

use App\Shared\Domain\DomainException;

final readonly class SocialLink
{
    public function __construct(
        public string $name,
        public string $url,
        public ?string $icon = null,
    ) {
        if (trim($name) === '') {
            throw new DomainException('Social link name is required.');
        }

        if (trim($url) === '') {
            throw new DomainException('Social link URL is required.');
        }
    }

    /** @return array{name: string, url: string, icon: ?string} */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'url' => $this->url,
            'icon' => $this->icon,
        ];
    }
}
