<?php

namespace App\Application\Documents\DTO;

final readonly class CompanyDocumentSnapshot
{
    /**
     * @param array<string, string> $placeholders
     */
    public function __construct(
        public array $placeholders,
    ) {}
}
