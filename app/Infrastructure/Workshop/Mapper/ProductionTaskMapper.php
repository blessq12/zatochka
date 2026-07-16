<?php

namespace App\Infrastructure\Workshop\Mapper;

use App\Application\Workshop\DTO\ProductionTaskDTO;
use App\Domain\Order\VO\OrderId;
use App\Domain\Workshop\Entity\Diagnosis;
use App\Domain\Workshop\Entity\MasterComment;
use App\Domain\Workshop\Entity\PerformedWork;
use App\Domain\Workshop\Entity\ProductionTask;
use App\Domain\Workshop\Entity\WorkExecution;
use App\Domain\Workshop\VO\ProductionStatus;
use App\Infrastructure\Workshop\Model\DiagnosisModel;
use App\Infrastructure\Workshop\Model\PerformedWorkModel;
use App\Infrastructure\Workshop\Model\ProductionTaskModel;
use App\Infrastructure\Workshop\Model\WorkExecutionModel;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final class ProductionTaskMapper
{
    public function toDomain(ProductionTaskModel $model): ProductionTask
    {
        $diagnosis = null;

        if ($model->diagnosis !== null) {
            $diagnosis = new Diagnosis(
                new EntityId((int) $model->diagnosis->id),
                (string) $model->diagnosis->summary,
                DateTimeImmutable::createFromInterface($model->diagnosis->completed_at),
                $model->diagnosis->technical_notes !== null
                    ? (string) $model->diagnosis->technical_notes
                    : null,
            );
        }

        $work = null;

        if ($model->workExecution !== null) {
            $work = WorkExecution::reconstitute(
                new EntityId((int) $model->workExecution->id),
                (string) $model->workExecution->description,
                DateTimeImmutable::createFromInterface($model->workExecution->started_at),
                $model->workExecution->completed_at !== null
                    ? DateTimeImmutable::createFromInterface($model->workExecution->completed_at)
                    : null,
            );
        }

        $comments = [];

        foreach ($model->master_comments ?? [] as $comment) {
            if (! is_array($comment)) {
                continue;
            }

            $comments[] = new MasterComment(
                new EntityId((int) $comment['id']),
                new EntityId((int) $comment['master_id']),
                (string) $comment['text'],
                isset($comment['created_at'])
                    ? new DateTimeImmutable((string) $comment['created_at'])
                    : new DateTimeImmutable(),
            );
        }

        $works = [];

        foreach ($model->performedWorks as $row) {
            $works[] = new PerformedWork(
                new EntityId((int) $row->id),
                new EntityId((int) $row->order_item_id),
                new EntityId((int) $row->master_id),
                (string) $row->description,
                $row->equipment_component_id !== null
                    ? new EntityId((int) $row->equipment_component_id)
                    : null,
                DateTimeImmutable::createFromInterface($row->created_at),
            );
        }

        return ProductionTask::reconstitute(
            new EntityId((int) $model->id),
            new OrderId((string) $model->order_id),
            ProductionStatus::from((string) $model->status),
            $model->master_id !== null ? new EntityId((int) $model->master_id) : null,
            $diagnosis,
            $work,
            $comments,
            $works,
        );
    }

    public function toPersistence(ProductionTask $task, ?ProductionTaskModel $model = null): ProductionTaskModel
    {
        $model ??= new ProductionTaskModel();
        $model->id = $task->id()->value;
        $model->order_id = $task->orderId()->value;
        $model->status = $task->status()->value;
        $model->master_id = $task->masterId()?->value;
        $model->master_comments = array_map(
            static fn (MasterComment $comment): array => [
                'id' => $comment->id->value,
                'master_id' => $comment->masterId->value,
                'text' => $comment->text,
                'created_at' => $comment->createdAt->format(DATE_ATOM),
            ],
            $task->comments(),
        );

        return $model;
    }

    public function diagnosisToPersistence(ProductionTask $task): ?DiagnosisModel
    {
        $diagnosis = $task->diagnosis();

        if ($diagnosis === null) {
            return null;
        }

        $row = new DiagnosisModel();
        $row->id = $diagnosis->id()->value;
        $row->production_task_id = $task->id()->value;
        $row->summary = $diagnosis->summary();
        $row->technical_notes = $diagnosis->technicalNotes();
        $row->completed_at = $diagnosis->completedAt();

        return $row;
    }

    public function workToPersistence(ProductionTask $task): ?WorkExecutionModel
    {
        $work = $task->workExecution();

        if ($work === null) {
            return null;
        }

        $row = new WorkExecutionModel();
        $row->id = $work->id()->value;
        $row->production_task_id = $task->id()->value;
        $row->description = $work->description();
        $row->started_at = $work->startedAt();
        $row->completed_at = $work->completedAt();

        return $row;
    }

    /** @return list<PerformedWorkModel> */
    public function worksToPersistence(ProductionTask $task): array
    {
        $rows = [];

        foreach ($task->works() as $work) {
            $row = new PerformedWorkModel();
            $row->id = $work->id->value;
            $row->production_task_id = $task->id()->value;
            $row->order_item_id = $work->orderItemId->value;
            $row->equipment_component_id = $work->equipmentComponentId?->value;
            $row->master_id = $work->masterId->value;
            $row->description = $work->description;
            $row->created_at = $work->createdAt;
            $rows[] = $row;
        }

        return $rows;
    }

    public function toDTO(ProductionTaskModel $model): ProductionTaskDTO
    {
        return new ProductionTaskDTO(
            (int) $model->id,
            (string) $model->order_id,
            (string) $model->status,
            $model->master_id !== null ? (int) $model->master_id : null,
            $model->diagnosis !== null ? (int) $model->diagnosis->id : null,
            $model->workExecution !== null ? (int) $model->workExecution->id : null,
        );
    }
}
