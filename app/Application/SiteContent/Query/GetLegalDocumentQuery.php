<?php

namespace App\Application\SiteContent\Query;

final readonly class GetLegalDocumentQuery
{
    public function __construct(
        public string $slug,
    ) {}
}
