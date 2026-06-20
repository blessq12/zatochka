<?php

namespace App\Application\SiteSettings\Command;

final readonly class SaveSiteSettingCommand
{
    /** @param array<string, mixed> $value */
    public function __construct(
        public string $key,
        public array $value,
    ) {}
}
