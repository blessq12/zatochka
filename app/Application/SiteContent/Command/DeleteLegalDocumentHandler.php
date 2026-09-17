<?php

namespace App\Application\SiteContent\Command;

use App\Domain\SiteContent\Repository\SiteContentRepository;

final readonly class DeleteLegalDocumentHandler
{
    public function __construct(
        private SiteContentRepository $siteContent,
    ) {}

    public function handle(string $slug): bool
    {
        return $this->siteContent->deleteLegalDocument($slug);
    }
}
