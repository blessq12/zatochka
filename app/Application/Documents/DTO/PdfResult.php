<?php

namespace App\Application\Documents\DTO;

final readonly class PdfResult
{
    public function __construct(
        public string $bytes,
        public string $filename,
    ) {}
}
