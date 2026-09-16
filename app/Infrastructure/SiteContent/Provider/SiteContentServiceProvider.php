<?php

namespace App\Infrastructure\SiteContent\Provider;

use App\Domain\SiteContent\Repository\SiteContentRepository;
use App\Infrastructure\SiteContent\Repository\MockSiteContentRepository;
use App\Providers\ContextServiceProvider;

final class SiteContentServiceProvider extends ContextServiceProvider
{
    protected function bindings(): array
    {
        return [
            SiteContentRepository::class => MockSiteContentRepository::class,
        ];
    }
}
