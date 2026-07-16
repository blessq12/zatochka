<?php

namespace Tests\Unit\Domain\Workshop;

use App\Domain\Order\VO\OrderId;
use App\Domain\Workshop\Entity\ProductionTask;
use App\Domain\Workshop\Entity\WorkExecution;
use App\Domain\Workshop\VO\ProductionStatus;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ProductionTaskFsmTest extends TestCase
{
    public function test_happy_path_to_completed(): void
    {
        $task = ProductionTask::open(new EntityId(1), OrderId::generate());
        $task->assignMaster(new EntityId(7));
        $task->startWork(new WorkExecution(new EntityId(2), 'work', new DateTimeImmutable()));
        $task->completeWork();
        $task->completeProduction();

        $this->assertSame(ProductionStatus::Completed, $task->status());
    }

    public function test_pause_and_resume(): void
    {
        $task = ProductionTask::open(new EntityId(1), OrderId::generate());
        $task->assignMaster(new EntityId(7));
        $task->startWork(new WorkExecution(new EntityId(2), 'work', new DateTimeImmutable()));
        $task->pauseForParts();
        $this->assertSame(ProductionStatus::WaitingParts, $task->status());

        $task->resumeFromParts();
        $this->assertSame(ProductionStatus::InWork, $task->status());
    }

    public function test_reopen_for_rework_from_completed(): void
    {
        $task = ProductionTask::open(new EntityId(1), OrderId::generate());
        $task->assignMaster(new EntityId(7));
        $task->startWork(new WorkExecution(new EntityId(2), 'work', new DateTimeImmutable()));
        $task->completeWork();
        $task->completeProduction();

        $task->reopenForRework();

        $this->assertSame(ProductionStatus::InWork, $task->status());
    }

    public function test_cannot_complete_production_from_in_work(): void
    {
        $task = ProductionTask::open(new EntityId(1), OrderId::generate());
        $task->assignMaster(new EntityId(7));
        $task->startWork(new WorkExecution(new EntityId(2), 'work', new DateTimeImmutable()));

        $this->expectException(DomainException::class);
        $task->completeProduction();
    }

    public function test_cancel_to_rejected(): void
    {
        $task = ProductionTask::open(new EntityId(1), OrderId::generate());
        $task->assignMaster(new EntityId(7));
        $task->cancel();

        $this->assertSame(ProductionStatus::Rejected, $task->status());
    }
}
