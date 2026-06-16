<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Domain\Catalog\Repository\BranchRepositoryInterface;
use App\Domain\ClientPortal\Exception\SiteLeadPolicyViolation;
use App\Domain\ClientPortal\Repository\SiteLeadRepositoryInterface;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Enum\OrderSource;
use App\Domain\OrderFulfillment\Event\OrderCreated;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;
use App\Domain\OrderFulfillment\Service\OrderNumberGenerator;
use RuntimeException;

final class CreateOrderHandler
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private SiteLeadRepositoryInterface $siteLeads,
        private BranchRepositoryInterface $branches,
        private OrderNumberGenerator $numberGenerator,
    ) {}

    public function handle(CreateOrderCommand $command): Order
    {
        $branchId = $command->branchId ?? $this->resolveBranchId();

        $order = Order::create(
            orderNumber: $this->numberGenerator->generate(),
            serviceTypes: $command->serviceTypes,
            source: $command->leadId !== null ? OrderSource::SiteLead : OrderSource::Manual,
            branchId: $branchId,
            clientId: $command->clientId,
            clientSnapshot: $command->clientSnapshot,
            leadId: $command->leadId,
            urgency: $command->urgency,
            isWarranty: $command->isWarranty,
            needsDelivery: $command->needsDelivery,
            deliveryAddress: $command->deliveryAddress,
            problemDescription: $command->problemDescription,
            equipmentId: $command->equipmentId,
            tools: $command->tools,
        );

        if ($command->leadId !== null) {
            $lead = $this->siteLeads->findById($command->leadId);

            if ($lead === null) {
                throw new RuntimeException('Заявка не найдена.');
            }

            if ($lead->isConverted()) {
                throw new SiteLeadPolicyViolation('Заявка уже конвертирована в заказ.');
            }

            $saved = $this->orders->save($order);
            $orderId = $saved->id();

            if ($orderId === null) {
                throw new RuntimeException('Не удалось сохранить заказ.');
            }

            $this->siteLeads->save($lead->markConverted($orderId));
            event(new OrderCreated($saved));

            return $saved;
        }

        $saved = $this->orders->save($order);
        event(new OrderCreated($saved));

        return $saved;
    }

    private function resolveBranchId(): int
    {
        $branch = $this->branches->findFirstActive();
        $branchId = $branch?->id();

        if ($branchId === null) {
            throw new RuntimeException('Активный филиал не найден.');
        }

        return $branchId;
    }
}
