<?php

namespace App\Domain\ClientPortal\Event;

use App\Domain\ClientPortal\Entity\SiteLead;

final readonly class SiteLeadReceived
{
    public function __construct(
        public SiteLead $lead,
    ) {}
}
