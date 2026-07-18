<?php

namespace App\Application\SiteContent\Command;

final readonly class UpdateCompanyProfileCommand
{
    public function __construct(
        public string $ownerName,
        public string $inn,
        public string $ogrn,
        public string $legalAddress,
        public string $actualAddress,
    ) {}
}
