<?php

namespace App\Application\Catalog\Command;

final readonly class SaveSiteSettingCommand
{
    /** @param array<string, mixed> $value */
    public function __construct(
        public string $key,
        public array $value,
    ) {}
}
