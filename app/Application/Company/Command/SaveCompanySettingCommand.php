<?php

namespace App\Application\Company\Command;

final readonly class SaveCompanySettingCommand
{
    /** @param array<string, mixed> $value */
    public function __construct(
        public string $key,
        public array $value,
    ) {}
}
