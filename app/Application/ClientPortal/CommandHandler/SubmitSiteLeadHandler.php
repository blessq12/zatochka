<?php

namespace App\Application\ClientPortal\CommandHandler;

use App\Application\ClientPortal\Command\SubmitSiteLeadCommand;
use App\Domain\ClientPortal\Entity\SiteLead;
use App\Domain\ClientPortal\Event\SiteLeadReceived;
use App\Domain\ClientPortal\Repository\SiteLeadRepositoryInterface;

final class SubmitSiteLeadHandler
{
    public function __construct(
        private SiteLeadRepositoryInterface $leads,
    ) {}

    public function handle(SubmitSiteLeadCommand $command): SiteLead
    {
        $lead = SiteLead::create(
            fullName: $command->fullName,
            phone: $command->phone,
            serviceTypes: $command->serviceTypes,
            email: $command->email,
            comment: $command->comment,
            intakeData: $command->intakeData,
            needsDelivery: $command->needsDelivery,
            deliveryAddress: $command->deliveryAddress,
        );

        $saved = $this->leads->save($lead);

        event(new SiteLeadReceived($saved));

        return $saved;
    }
}
