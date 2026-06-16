<?php

namespace App\Application\ClientPortal\CommandHandler;

use App\Application\ClientPortal\Command\LinkGuestOrdersToClientCommand;
use App\Application\ClientPortal\Support\ClientLoader;
use App\Domain\ClientPortal\Event\GuestOrdersLinkedToClient;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class LinkGuestOrdersToClientHandler
{
    public function __construct(
        private ClientLoader $clientLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(LinkGuestOrdersToClientCommand $command): int
    {
        $client = $this->clientLoader->load($command->clientId);

        $count = $this->orders->linkGuestOrdersByPhone(
            $client->id() ?? $command->clientId,
            $client->phone(),
        );

        event(new GuestOrdersLinkedToClient($command->clientId, $count));

        return $count;
    }
}
