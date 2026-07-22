<?php

namespace Tests\Unit\Application\Workshop;

use App\Application\Workshop\DTO\OrderProductionContextDTO;
use App\Application\Workshop\DTO\OrderProductionItemDTO;
use App\Application\Workshop\ServiceType\RepairProductionCompletionPolicy;
use App\Application\Workshop\ServiceType\SharpeningProductionCompletionPolicy;
use App\Domain\Order\VO\OrderId;
use App\Domain\Workshop\Entity\PerformedWork;
use App\Domain\Workshop\Entity\ProductionTask;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use PHPUnit\Framework\TestCase;

final class ProductionCompletionPolicyTest extends TestCase
{
    public function test_sharpening_requires_work_per_item(): void
    {
        $itemId = 11;
        $context = $this->sharpeningContext($itemId);
        $task = ProductionTask::open(new EntityId(1), new OrderId($context->orderId));
        $task->assignMaster(new EntityId(5));

        $this->expectException(DomainException::class);
        (new SharpeningProductionCompletionPolicy())->assertReadyToFinish($context, $task);
    }

    public function test_sharpening_passes_when_work_exists(): void
    {
        $itemId = 11;
        $context = $this->sharpeningContext($itemId);
        $task = ProductionTask::open(new EntityId(1), new OrderId($context->orderId));
        $masterId = new EntityId(5);
        $task->assignMaster($masterId);
        $task->addWork(new PerformedWork(new EntityId(99), new EntityId($itemId), $masterId, 'заточка'));

        (new SharpeningProductionCompletionPolicy())->assertReadyToFinish($context, $task);
        $this->assertTrue(true);
    }

    public function test_sharpening_passes_when_all_items_fully_rejected_without_works(): void
    {
        $itemId = 11;
        $context = new OrderProductionContextDTO(
            OrderId::generate()->value,
            'sharpening',
            'in_progress',
            [new OrderProductionItemDTO($itemId, null, true)],
        );
        $task = ProductionTask::open(new EntityId(1), new OrderId($context->orderId));
        $task->assignMaster(new EntityId(5));

        (new SharpeningProductionCompletionPolicy())->assertReadyToFinish($context, $task);
        $this->assertTrue(true);
    }

    public function test_repair_requires_work_per_equipment_item(): void
    {
        $itemId = 21;
        $context = $this->repairContext($itemId);
        $task = ProductionTask::open(new EntityId(2), new OrderId($context->orderId));
        $task->assignMaster(new EntityId(5));

        $this->expectException(DomainException::class);
        (new RepairProductionCompletionPolicy())->assertReadyToFinish($context, $task);
    }

    public function test_repair_passes_when_component_work_exists(): void
    {
        $itemId = 21;
        $context = $this->repairContext($itemId);
        $task = ProductionTask::open(new EntityId(2), new OrderId($context->orderId));
        $masterId = new EntityId(5);
        $task->assignMaster($masterId);
        $task->addWork(new PerformedWork(
            new EntityId(99),
            new EntityId($itemId),
            $masterId,
            'замена подшипника',
            new EntityId(77),
        ));

        (new RepairProductionCompletionPolicy())->assertReadyToFinish($context, $task);
        $this->assertTrue(true);
    }

    private function sharpeningContext(int $itemId): OrderProductionContextDTO
    {
        return new OrderProductionContextDTO(
            OrderId::generate()->value,
            'sharpening',
            'in_progress',
            [new OrderProductionItemDTO($itemId, null, false)],
        );
    }

    private function repairContext(int $itemId): OrderProductionContextDTO
    {
        return new OrderProductionContextDTO(
            OrderId::generate()->value,
            'repair',
            'in_progress',
            [new OrderProductionItemDTO($itemId, 50, false)],
        );
    }
}
