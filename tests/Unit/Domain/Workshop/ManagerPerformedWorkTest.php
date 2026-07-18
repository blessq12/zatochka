<?php

namespace Tests\Unit\Domain\Workshop;

use App\Domain\Order\VO\OrderId;
use App\Domain\Workshop\Entity\PerformedWork;
use App\Domain\Workshop\Entity\ProductionTask;
use App\Domain\Workshop\Entity\WorkExecution;
use App\Domain\Workshop\Event\PerformedWorkRemoved;
use App\Domain\Workshop\VO\ProductionStatus;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ManagerPerformedWorkTest extends TestCase
{
    public function test_manager_can_add_remove_and_change_description_on_completed_task(): void
    {
        $task = $this->completedTask();

        $task->managerAddWork(new PerformedWork(
            new EntityId(100),
            new EntityId(1),
            new EntityId(7),
            'Initial work',
        ));

        $this->assertCount(1, $task->works());

        $task->changeWorkDescription(new EntityId(100), 'Updated work');
        $this->assertSame('Updated work', $task->works()[0]->description);

        $task->managerRemoveWork(new EntityId(100));
        $this->assertSame([], $task->works());

        $events = $task->pullDomainEvents();
        $this->assertInstanceOf(PerformedWorkRemoved::class, $events[array_key_last($events)]);
    }

    public function test_manager_cannot_edit_before_work_completion(): void
    {
        $task = ProductionTask::open(new EntityId(1), OrderId::generate());
        $task->assignMaster(new EntityId(7));

        $this->expectException(DomainException::class);
        $task->managerAddWork(new PerformedWork(
            new EntityId(100),
            new EntityId(1),
            new EntityId(7),
            'Too early',
        ));
    }

    public function test_master_remove_emits_performed_work_removed(): void
    {
        $task = ProductionTask::open(new EntityId(1), OrderId::generate());
        $task->assignMaster(new EntityId(7));
        $task->startWork(new WorkExecution(new EntityId(2), 'work', new DateTimeImmutable()));
        $task->addWork(new PerformedWork(
            new EntityId(100),
            new EntityId(1),
            new EntityId(7),
            'Master work',
        ));

        $task->removeWork(new EntityId(100), new EntityId(7));

        $events = $task->pullDomainEvents();
        $this->assertInstanceOf(PerformedWorkRemoved::class, $events[array_key_last($events)]);
    }

    private function completedTask(): ProductionTask
    {
        $task = ProductionTask::open(new EntityId(1), OrderId::generate());
        $task->assignMaster(new EntityId(7));
        $task->startWork(new WorkExecution(new EntityId(2), 'work', new DateTimeImmutable()));
        $task->completeWork();
        $task->completeProduction();
        $task->pullDomainEvents();

        $this->assertSame(ProductionStatus::Completed, $task->status());

        return $task;
    }
}
