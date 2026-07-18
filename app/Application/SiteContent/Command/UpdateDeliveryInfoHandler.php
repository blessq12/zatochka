<?php

namespace App\Application\SiteContent\Command;

use App\Domain\SiteContent\Entity\DeliveryAdvantage;
use App\Domain\SiteContent\Repository\DeliveryInfoRepository;

final readonly class UpdateDeliveryInfoHandler
{
    public function __construct(
        private DeliveryInfoRepository $deliveryInfos,
    ) {}

    public function handle(UpdateDeliveryInfoCommand $command): void
    {
        $advantages = [];

        foreach ($command->advantages as $advantage) {
            $advantages[] = new DeliveryAdvantage(
                (string) ($advantage['title'] ?? ''),
                (string) ($advantage['description'] ?? ''),
            );
        }

        $aggregate = $this->deliveryInfos->get();
        $aggregate->update($command->freeConditions, $advantages);
        $this->deliveryInfos->save($aggregate);
    }
}
