<?php

namespace Tests\Unit\Application\Workshop;

use App\Application\Workshop\ServiceType\RepairProductionCompletionPolicy;
use App\Application\Workshop\ServiceType\SharpeningProductionCompletionPolicy;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderNumber;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderUrgency;
use App\Domain\Order\VO\SharpeningToolType;
use App\Domain\Workshop\Entity\PerformedWork;
use App\Domain\Workshop\Entity\ProductionTask;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class ProductionCompletionPolicyTest extends TestCase
{
    public function test_sharpening_requires_work_per_item(): void
    {
        $itemId = new EntityId(11);
        $order = $this->sharpeningOrder($itemId);
        $task = ProductionTask::open(new EntityId(1), $order->id());
        $task->assignMaster(new EntityId(5));

        $this->expectException(DomainException::class);
        (new SharpeningProductionCompletionPolicy())->assertReadyToFinish($order, $task);
    }

    public function test_sharpening_passes_when_work_exists(): void
    {
        $itemId = new EntityId(11);
        $order = $this->sharpeningOrder($itemId);
        $task = ProductionTask::open(new EntityId(1), $order->id());
        $masterId = new EntityId(5);
        $task->assignMaster($masterId);
        $task->addWork(new PerformedWork(new EntityId(99), $itemId, $masterId, 'заточка'));

        (new SharpeningProductionCompletionPolicy())->assertReadyToFinish($order, $task);
        $this->assertTrue(true);
    }

    public function test_repair_requires_work_per_equipment_item(): void
    {
        $itemId = new EntityId(21);
        $order = $this->repairOrder($itemId);
        $task = ProductionTask::open(new EntityId(2), $order->id());
        $task->assignMaster(new EntityId(5));

        $this->expectException(DomainException::class);
        (new RepairProductionCompletionPolicy())->assertReadyToFinish($order, $task);
    }

    public function test_repair_passes_when_component_work_exists(): void
    {
        $itemId = new EntityId(21);
        $order = $this->repairOrder($itemId);
        $task = ProductionTask::open(new EntityId(2), $order->id());
        $masterId = new EntityId(5);
        $task->assignMaster($masterId);
        $task->addWork(new PerformedWork(
            new EntityId(99),
            $itemId,
            $masterId,
            'замена подшипника',
            new EntityId(77),
        ));

        (new RepairProductionCompletionPolicy())->assertReadyToFinish($order, $task);
        $this->assertTrue(true);
    }

    private function sharpeningOrder(EntityId $itemId): Order
    {
        return Order::create(
            OrderId::generate(),
            new EntityId(1),
            new Money('500.00'),
            [OrderItem::forTool($itemId, 'Нож', SharpeningToolType::KitchenKnife, 1)],
            OrderServiceType::Sharpening,
            OrderBillingType::Paid,
            OrderUrgency::Normal,
            number: new OrderNumber('ORD-26-10'),
        );
    }

    private function repairOrder(EntityId $itemId): Order
    {
        return Order::create(
            OrderId::generate(),
            new EntityId(1),
            new Money('1500.00'),
            [OrderItem::forEquipment($itemId, new EntityId(50))],
            OrderServiceType::Repair,
            OrderBillingType::Paid,
            OrderUrgency::Normal,
            number: new OrderNumber('ORD-26-11'),
        );
    }
}
