<?php

namespace App\Application\ClientPortal\CommandHandler;

use App\Application\ClientPortal\Command\LinkGuestOrderToClientCommand;
use App\Application\ClientPortal\Support\ClientLoader;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\ClientPortal\Event\GuestOrdersLinkedToClient;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class LinkGuestOrderToClientHandler
{
    public function __construct(
        private ClientLoader $clientLoader,
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(LinkGuestOrderToClientCommand $command): void
    {
        $this->clientLoader->load($command->clientId);

        $order = $this->orderLoader->load($command->orderId);
        $linked = $order->linkToClient($command->clientId);

        $this->orders->save($linked);

        event(new GuestOrdersLinkedToClient($command->clientId, 1));
    }
}
