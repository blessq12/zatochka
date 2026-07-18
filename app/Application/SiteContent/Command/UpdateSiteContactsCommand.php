<?php

namespace App\Application\SiteContent\Command;

final readonly class UpdateSiteContactsCommand
{
    /**
     * @param  list<string>  $addressDetails
     * @param  list<array{name: string, url: string, icon?: ?string}>  $socialLinks
     */
    public function __construct(
        public string $contactPerson,
        public string $phone,
        public string $phoneTel,
        public string $email,
        public string $addressMain,
        public array $addressDetails,
        public array $socialLinks,
    ) {}
}
