<?php

namespace Tests\Unit\Domain\OrderFulfillment;

use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Entity\OrderWork;
use App\Domain\OrderFulfillment\Enum\OrderSource;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Domain\OrderFulfillment\Exception\OrderPolicyViolation;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use App\Domain\OrderFulfillment\ValueObject\OrderNumber;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    private const MASTER_ID = 42;

    public function test_полный_жизненный_цикл_заказа(): void
    {
        $now = new DateTimeImmutable('2026-06-16 12:00:00');

        $order = $this->newOrder()
            ->assignMaster(self::MASTER_ID)
            ->takeToWork(self::MASTER_ID, $now)
            ->addWork(new OrderWork(null, 'Заточка ножниц', null, 0))
            ->markReady($now)
            ->issue($now);

        $this->assertSame(OrderStatus::Issued, $order->status());
        $this->assertNotNull($order->takenAt());
        $this->assertNotNull($order->readyAt());
        $this->assertNotNull($order->issuedAt());
    }

    public function test_нельзя_взять_в_работу_без_мастера(): void
    {
        $this->expectException(OrderPolicyViolation::class);

        $this->newOrder()->takeToWork(self::MASTER_ID, new DateTimeImmutable);
    }

    public function test_нельзя_завершить_без_работ(): void
    {
        $this->expectException(OrderPolicyViolation::class);

        $this->newOrder()
            ->assignMaster(self::MASTER_ID)
            ->takeToWork(self::MASTER_ID, new DateTimeImmutable)
            ->markReady(new DateTimeImmutable);
    }

    public function test_отмена_только_из_new(): void
    {
        $cancelled = $this->newOrder()->cancel();
        $this->assertSame(OrderStatus::Cancelled, $cancelled->status());

        $this->expectException(OrderPolicyViolation::class);

        $this->newOrder()
            ->assignMaster(self::MASTER_ID)
            ->takeToWork(self::MASTER_ID, new DateTimeImmutable)
            ->cancel();
    }

    public function test_гостевой_заказ_требует_снимок_клиента(): void
    {
        $this->expectException(OrderPolicyViolation::class);

        Order::create(
            orderNumber: new OrderNumber('ORD-2026-0001'),
            serviceTypes: ['sharpening'],
            source: OrderSource::Manual,
            branchId: 1,
        );
    }

    public function test_пересчёт_цены(): void
    {
        $order = $this->newOrder()
            ->assignMaster(self::MASTER_ID)
            ->takeToWork(self::MASTER_ID, new DateTimeImmutable)
            ->addWork(new OrderWork(null, 'Работа', '500.00', 0))
            ->setWorkPrice(0, '1500.50')
            ->recalculatePrice();

        $this->assertSame('1500.50', $order->price());
    }

    public function test_нельзя_добавить_работу_в_ожидании_запчастей(): void
    {
        $order = $this->newOrder()
            ->assignMaster(self::MASTER_ID)
            ->takeToWork(self::MASTER_ID, new DateTimeImmutable)
            ->markWaitingForParts();

        $this->expectException(OrderPolicyViolation::class);

        $order->addWork(new OrderWork(null, 'Замена детали', null, 0));
    }

    public function test_нельзя_завершить_из_ожидания_запчастей(): void
    {
        $order = $this->newOrder()
            ->assignMaster(self::MASTER_ID)
            ->takeToWork(self::MASTER_ID, new DateTimeImmutable)
            ->addWork(new OrderWork(null, 'Диагностика', null, 0))
            ->markWaitingForParts();

        $this->expectException(OrderPolicyViolation::class);

        $order->markReady(new DateTimeImmutable);
    }

    public function test_ожидание_запчастей_возвращается_в_работу(): void
    {
        $order = $this->newOrder()
            ->assignMaster(self::MASTER_ID)
            ->takeToWork(self::MASTER_ID, new DateTimeImmutable)
            ->markWaitingForParts()
            ->resume();

        $this->assertSame(OrderStatus::InWork, $order->status());
    }

    private function newOrder(): Order
    {
        return Order::create(
            orderNumber: new OrderNumber('ORD-2026-0001'),
            serviceTypes: ['sharpening'],
            source: OrderSource::Manual,
            branchId: 1,
            clientSnapshot: new ClientSnapshot(['full_name' => 'Иван', 'phone' => '+79001234567']),
        );
    }
}
