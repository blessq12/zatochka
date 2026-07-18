<?php

namespace App\Domain\Workshop\Entity;

use App\Domain\Order\VO\OrderId;
use App\Domain\Workshop\Event\DiagnosisCompleted;
use App\Domain\Workshop\Event\MasterAssigned;
use App\Domain\Workshop\Event\PerformedWorkRemoved;
use App\Domain\Workshop\Event\ProductionCancelled;
use App\Domain\Workshop\Event\ProductionCompleted;
use App\Domain\Workshop\Event\WorkCompleted;
use App\Domain\Workshop\Event\WorkStarted;
use App\Domain\Workshop\VO\ProductionStatus;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class ProductionTask extends AggregateRoot
{
    private ProductionStatus $status;
    private ?EntityId $masterId = null;
    private ?Diagnosis $diagnosis = null;
    private ?WorkExecution $workExecution = null;

    /** @var list<MasterComment> */
    private array $comments = [];

    /** @var list<PerformedWork> */
    private array $works = [];

    private function __construct(
        private readonly EntityId $id,
        private readonly OrderId $orderId,
    ) {
        $this->status = ProductionStatus::Queued;
    }

    public static function open(EntityId $id, OrderId $orderId): self
    {
        return new self($id, $orderId);
    }

    /**
     * @param list<MasterComment> $comments
     * @param list<PerformedWork> $works
     */
    public static function reconstitute(
        EntityId $id,
        OrderId $orderId,
        ProductionStatus $status,
        ?EntityId $masterId = null,
        ?Diagnosis $diagnosis = null,
        ?WorkExecution $workExecution = null,
        array $comments = [],
        array $works = [],
    ): self {
        $task = new self($id, $orderId);
        $task->status = $status;
        $task->masterId = $masterId;
        $task->diagnosis = $diagnosis;
        $task->workExecution = $workExecution;
        $task->comments = $comments;
        $task->works = $works;

        return $task;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function orderId(): OrderId
    {
        return $this->orderId;
    }

    public function status(): ProductionStatus
    {
        return $this->status;
    }

    public function masterId(): ?EntityId
    {
        return $this->masterId;
    }

    public function diagnosis(): ?Diagnosis
    {
        return $this->diagnosis;
    }

    public function workExecution(): ?WorkExecution
    {
        return $this->workExecution;
    }

    /** @return list<MasterComment> */
    public function comments(): array
    {
        return $this->comments;
    }

    /** @return list<PerformedWork> */
    public function works(): array
    {
        return $this->works;
    }

    public function assignMaster(EntityId $masterId): void
    {
        $this->assertNotTerminal();

        if ($this->masterId !== null) {
            throw new DomainException('Master is already assigned to this production task.');
        }

        $this->masterId = $masterId;
        $this->transitionTo(ProductionStatus::MasterAssigned);
        $this->record(new MasterAssigned($this->id, $this->orderId, $masterId));
    }

    public function completeDiagnosis(Diagnosis $diagnosis): void
    {
        $this->assertNotTerminal();

        if ($this->status !== ProductionStatus::MasterAssigned) {
            throw new DomainException('Diagnosis requires an assigned master.');
        }

        if ($this->diagnosis !== null) {
            throw new DomainException('Diagnosis is already completed.');
        }

        $this->diagnosis = $diagnosis;
        $this->transitionTo(ProductionStatus::Diagnosed);
        $this->record(new DiagnosisCompleted($this->id, $this->orderId, $diagnosis->id()));
    }

    public function startWork(WorkExecution $workExecution): void
    {
        $this->assertNotTerminal();

        if (! in_array($this->status, [ProductionStatus::MasterAssigned, ProductionStatus::Diagnosed], true)) {
            throw new DomainException('Work can start only after master assignment (diagnosis optional).');
        }

        if ($this->workExecution !== null) {
            throw new DomainException('Work execution is already started.');
        }

        $this->workExecution = $workExecution;
        $this->transitionTo(ProductionStatus::InWork);
        $this->record(new WorkStarted($this->id, $this->orderId, $workExecution->id()));
    }

    public function pauseForParts(): void
    {
        $this->assertNotTerminal();

        if ($this->status !== ProductionStatus::InWork) {
            throw new DomainException('Waiting for parts is allowed only while work is in progress.');
        }

        $this->transitionTo(ProductionStatus::WaitingParts);
    }

    public function resumeFromParts(): void
    {
        $this->assertNotTerminal();

        if ($this->status !== ProductionStatus::WaitingParts) {
            throw new DomainException('Resume is allowed only from waiting for parts.');
        }

        $this->transitionTo(ProductionStatus::InWork);
    }

    public function completeWork(): void
    {
        $this->assertNotTerminal();

        if ($this->status !== ProductionStatus::InWork || $this->workExecution === null) {
            throw new DomainException('Work can be completed only while in progress.');
        }

        $this->workExecution->complete();
        $this->transitionTo(ProductionStatus::WorkCompleted);
        $this->record(new WorkCompleted($this->id, $this->orderId, $this->workExecution->id()));
    }

    public function completeProduction(): void
    {
        $this->assertNotTerminal();

        if ($this->status !== ProductionStatus::WorkCompleted) {
            throw new DomainException('Production can be completed only after work completion.');
        }

        $this->transitionTo(ProductionStatus::Completed);
        $this->record(new ProductionCompleted($this->id, $this->orderId));
    }

    public function cancel(): void
    {
        if ($this->status === ProductionStatus::Rejected) {
            return;
        }

        $this->transitionTo(ProductionStatus::Rejected);
        $this->record(new ProductionCancelled($this->id, $this->orderId));
    }

    public function reopenForRework(): void
    {
        if ($this->status === ProductionStatus::InWork) {
            return;
        }

        if (! in_array($this->status, [ProductionStatus::Completed, ProductionStatus::WorkCompleted], true)) {
            throw new DomainException('Production task can be reopened only after completion.');
        }

        $this->workExecution?->reopen();
        $this->transitionTo(ProductionStatus::InWork);
    }

    public function addComment(MasterComment $comment): void
    {
        $this->assertNotTerminal();

        if ($this->masterId === null || ! $this->masterId->equals($comment->masterId)) {
            throw new DomainException('Only the assigned master can leave technical comments.');
        }

        $this->comments[] = $comment;
    }

    public function removeComment(EntityId $commentId, EntityId $masterId): void
    {
        $this->assertNotTerminal();

        if ($this->masterId === null || ! $this->masterId->equals($masterId)) {
            throw new DomainException('Only the assigned master can remove technical comments.');
        }

        $before = count($this->comments);
        $this->comments = array_values(array_filter(
            $this->comments,
            static fn (MasterComment $comment): bool => ! $comment->id->equals($commentId),
        ));

        if (count($this->comments) === $before) {
            throw new DomainException('Master comment not found.');
        }
    }

    public function addWork(PerformedWork $work): void
    {
        $this->assertNotTerminal();

        if ($this->masterId === null || ! $this->masterId->equals($work->masterId)) {
            throw new DomainException('Only the assigned master can add performed works.');
        }

        $this->works[] = $work;
    }

    public function removeWork(EntityId $workId, EntityId $masterId): void
    {
        $this->assertNotTerminal();

        if ($this->masterId === null || ! $this->masterId->equals($masterId)) {
            throw new DomainException('Only the assigned master can remove performed works.');
        }

        $this->removeWorkInternal($workId);
    }

    public function managerAddWork(PerformedWork $work): void
    {
        $this->assertManagerWorkEditable();

        if ($this->masterId === null) {
            throw new DomainException('Cannot add performed work without an assigned master.');
        }

        if (! $this->masterId->equals($work->masterId)) {
            throw new DomainException('Performed work must be attributed to the assigned master.');
        }

        $this->works[] = $work;
    }

    public function managerRemoveWork(EntityId $workId): void
    {
        $this->assertManagerWorkEditable();
        $this->removeWorkInternal($workId);
    }

    public function changeWorkDescription(EntityId $workId, string $description): void
    {
        $this->assertManagerWorkEditable();

        foreach ($this->works as $index => $work) {
            if (! $work->id->equals($workId)) {
                continue;
            }

            $this->works[$index] = $work->withDescription($description);

            return;
        }

        throw new DomainException('Performed work not found.');
    }

    private function removeWorkInternal(EntityId $workId): void
    {
        $before = count($this->works);
        $this->works = array_values(array_filter(
            $this->works,
            static fn (PerformedWork $work): bool => ! $work->id->equals($workId),
        ));

        if (count($this->works) === $before) {
            throw new DomainException('Performed work not found.');
        }

        $this->record(new PerformedWorkRemoved($this->id, $this->orderId, $workId));
    }

    private function transitionTo(ProductionStatus $next): void
    {
        if (! $this->status->canTransitionTo($next)) {
            throw new DomainException(sprintf(
                'Production status transition from %s to %s is not allowed.',
                $this->status->value,
                $next->value,
            ));
        }

        $this->status = $next;
    }

    private function assertNotTerminal(): void
    {
        if ($this->status->isTerminal()) {
            throw new DomainException('Terminal production task cannot be modified.');
        }
    }

    private function assertManagerWorkEditable(): void
    {
        if ($this->status === ProductionStatus::Rejected) {
            throw new DomainException('Rejected production task cannot be modified.');
        }

        if (! in_array($this->status, [
            ProductionStatus::WorkCompleted,
            ProductionStatus::Completed,
        ], true)) {
            throw new DomainException('Manager can edit performed works only after work completion.');
        }
    }
}
