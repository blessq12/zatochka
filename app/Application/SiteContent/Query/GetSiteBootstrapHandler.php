<?php

namespace App\Application\SiteContent\Query;

use App\Application\SiteContent\Presenter\SiteContentPresenter;
use App\Domain\SiteContent\Repository\SiteContentRepository;

final readonly class GetSiteBootstrapHandler
{
    public function __construct(
        private SiteContentRepository $siteContent,
        private SiteContentPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(GetSiteBootstrapQuery $query): array
    {
        return $this->presenter->present($this->siteContent->bootstrap());
    }
}
