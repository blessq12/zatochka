<?php

namespace App\Application\SiteContent\Command;

use App\Domain\SiteContent\Repository\SiteContentRepository;

final readonly class SaveLegalDocumentHandler
{
    public function __construct(
        private SiteContentRepository $siteContent,
    ) {}

    /**
     * @param  array{slug: string, type?: string, title: string, body_html: string}  $data
     * @return array{type: string, slug: string, title: string, body_html: string, updated_at: string}
     */
    public function handle(array $data): array
    {
        return $this->siteContent->saveLegalDocument([
            'slug' => $data['slug'],
            'type' => $data['type'] ?? $data['slug'],
            'title' => $data['title'],
            'body_html' => $data['body_html'],
        ]);
    }
}
