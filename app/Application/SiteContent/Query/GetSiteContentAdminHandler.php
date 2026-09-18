<?php

namespace App\Application\SiteContent\Query;

use App\Application\SiteContent\Presenter\SiteContentPresenter;
use App\Domain\SiteContent\Repository\SiteContentRepository;

final readonly class GetSiteContentAdminHandler
{
    public function __construct(
        private SiteContentRepository $siteContent,
        private SiteContentPresenter $presenter,
    ) {}

    /**
     * @return array{raw: array<string, mixed>, presented: array<string, mixed>, legal: list<array<string, mixed>>}
     */
    public function handle(): array
    {
        $raw = $this->siteContent->bootstrap();

        return [
            'raw' => $raw,
            'presented' => $this->presenter->present($raw),
            'legal' => $this->siteContent->legalDocuments(),
        ];
    }
}
