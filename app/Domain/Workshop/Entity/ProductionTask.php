<?php

namespace App\Domain\Workshop\Entity;

use App\Domain\Workshop\Event\DiagnosisCompleted;
use App\Domain\Workshop\Event\ElementRejected;
use App\Domain\Workshop\Event\MasterAssigned;
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

    private function __construct(
        private readonly EntityId $id,
        private readonly EntityId $orderItemId,
    ) {
        $this->status = ProductionStatus::Queued;
    }

    public static function open(EntityId $id, EntityId $orderItemId): self
    {
        return new self($id, $orderItemId);
    }

    /**
     * @param list<MasterComment> $comments
     */
    public static function reconstitute(
        EntityId $id,
        EntityId $orderItemId,
        ProductionStatus $status,
        ?EntityId $masterId = null,
        ?Diagnosis $diagnosis = null,
        ?WorkExecution $workExecution = null,
        array $comments = [],
    ): self {
        $task = new self($id, $orderItemId);
        $task->status = $status;
        $task->masterId = $masterId;
        $task->diagnosis = $diagnosis;
        $task->workExecution = $workExecution;
        $task->comments = $comments;

        return $task;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function orderItemId(): EntityId
    {
        return $this->orderItemId;
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

    public function assignMaster(EntityId $masterId): void
    {
        $this->assertNotTerminal();

        if ($this->masterId !== null) {
            throw new DomainException('Master is already assigned to this production task.');
        }

        $this->masterId = $masterId;
        $this->transitionTo(ProductionStatus::MasterAssigned);
        $this->record(new MasterAssigned($this->id, $this->orderItemId, $masterId));
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
        $this->record(new DiagnosisCompleted($this->id, $this->orderItemId, $diagnosis->id()));
    }

    public function reject(string $reason): void
    {
        $this->assertNotTerminal();

        if (trim($reason) === '') {
            throw new DomainException('Rejection reason is required.');
        }

        $this->transitionTo(ProductionStatus::Rejected);
        $this->record(new ElementRejected($this->id, $this->orderItemId, $reason));
    }

    public function startWork(WorkExecution $workExecution): void
    {
        $this->assertNotTerminal();

        if ($this->status !== ProductionStatus::Diagnosed) {
            throw new DomainException('Work can start only after diagnosis.');
        }

        if ($this->workExecution !== null) {
            throw new DomainException('Work execution is already started.');
        }

        $this->workExecution = $workExecution;
        $this->transitionTo(ProductionStatus::InWork);
        $this->record(new WorkStarted($this->id, $this->orderItemId, $workExecution->id()));
    }

    public function completeWork(): void
    {
        $this->assertNotTerminal();

        if ($this->status !== ProductionStatus::InWork || $this->workExecution === null) {
            throw new DomainException('Work can be completed only while in progress.');
        }

        $this->workExecution->complete();
        $this->transitionTo(ProductionStatus::WorkCompleted);
        $this->record(new WorkCompleted($this->id, $this->orderItemId, $this->workExecution->id()));
    }

    public function completeProduction(): void
    {
        $this->assertNotTerminal();

        if ($this->status !== ProductionStatus::WorkCompleted) {
            throw new DomainException('Production can be completed only after work completion.');
        }

        $this->transitionTo(ProductionStatus::Completed);
        $this->record(new ProductionCompleted($this->id, $this->orderItemId));
    }

    public function addComment(MasterComment $comment): void
    {
        $this->assertNotTerminal();

        if ($this->masterId === null || ! $this->masterId->equals($comment->masterId)) {
            throw new DomainException('Only the assigned master can leave technical comments.');
        }

        $this->comments[] = $comment;
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
}
