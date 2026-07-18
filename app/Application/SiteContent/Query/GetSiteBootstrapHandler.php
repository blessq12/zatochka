<?php

namespace App\Application\SiteContent\Query;

use App\Application\SiteContent\ReadPort\SiteBootstrapReadPort;

final readonly class GetSiteBootstrapHandler
{
    public function __construct(
        private SiteBootstrapReadPort $bootstrap,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        return $this->bootstrap->getBootstrap();
    }
}
