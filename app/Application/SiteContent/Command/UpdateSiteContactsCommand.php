<?php

namespace App\Application\SiteContent\Command;

final readonly class UpdateSiteContactsCommand
{
    /**
     * @param  list<array{name: string, url: string, icon?: ?string}>  $socialLinks
     */
    public function __construct(
        public string $contactPerson,
        public string $phone,
        public string $email,
        public string $addressMain,
        public string $entranceDirections,
        public array $socialLinks,
    ) {}
}
