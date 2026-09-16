<?php

namespace App\Application\SiteContent\Query;

use App\Domain\SiteContent\Repository\SiteContentRepository;

final readonly class GetLegalDocumentHandler
{
    public function __construct(
        private SiteContentRepository $siteContent,
    ) {}

    /**
     * @return array{type: string, slug: string, title: string, body_html: string, updated_at: string}|null
     */
    public function handle(GetLegalDocumentQuery $query): ?array
    {
        return $this->siteContent->legalDocumentBySlug($query->slug);
    }
}
