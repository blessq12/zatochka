<?php

namespace Tests\Unit\Domain\Order;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderNumber;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Order\VO\OrderUrgency;
use App\Domain\Order\VO\SharpeningToolType;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class OrderFsmTest extends TestCase
{
    public function test_happy_path_to_ready_and_issue(): void
    {
        $order = $this->newSharpeningOrder();

        $order->assignMaster(new EntityId(10));
        $this->assertSame(OrderStatus::MasterAssigned, $order->status());

        $order->markInProgress();
        $this->assertSame(OrderStatus::InProgress, $order->status());

        $order->markWorksCompleted();
        $this->assertSame(OrderStatus::WorksCompleted, $order->status());

        $order->markReady();
        $this->assertSame(OrderStatus::Ready, $order->status());

        $order->issue();
        $this->assertSame(OrderStatus::Issued, $order->status());
    }

    public function test_return_to_master_from_works_completed(): void
    {
        $order = $this->newSharpeningOrder();
        $order->assignMaster(new EntityId(10));
        $order->markInProgress();
        $order->markWorksCompleted();

        $order->returnToMasterWork('Переделать фаску');

        $this->assertSame(OrderStatus::InProgress, $order->status());
        $this->assertSame('Переделать фаску', $order->managerReworkComment());
    }

    public function test_cannot_issue_before_ready(): void
    {
        $order = $this->newSharpeningOrder();

        $this->expectException(DomainException::class);
        $order->issue();
    }

    public function test_cannot_return_to_master_before_works_completed(): void
    {
        $order = $this->newSharpeningOrder();
        $order->assignMaster(new EntityId(10));

        $this->expectException(DomainException::class);
        $order->returnToMasterWork('too early');
    }

    public function test_cancel_emits_order_cancelled(): void
    {
        $order = $this->newSharpeningOrder();
        $order->cancel('client refused');

        $this->assertSame(OrderStatus::Cancelled, $order->status());
        $events = $order->pullDomainEvents();
        $types = array_map(static fn (object $e): string => $e::class, $events);
        $this->assertContains(\App\Domain\Order\Event\OrderCancelled::class, $types);
    }

    private function newSharpeningOrder(): Order
    {
        return Order::create(
            OrderId::generate(),
            new EntityId(1),
            new Money('1000.00'),
            [
                OrderItem::forTool(
                    new EntityId(1),
                    'Нож',
                    SharpeningToolType::KitchenKnife,
                    1,
                ),
            ],
            OrderServiceType::Sharpening,
            OrderBillingType::Paid,
            OrderUrgency::Normal,
            number: new OrderNumber('ORD-26-1'),
        );
    }
}
