<?php

namespace App\Infrastructure\Repair\Mapper;

use App\Domain\Repair\Entity\Repair;
use App\Domain\Repair\Mapper\RepairMapper;
use App\Models\Repair as RepairModel;

class RepairMapperImpl implements RepairMapper
{
    public function toDomain(RepairModel $model): Repair
    {
        return new Repair(
            id: $model->id,
            number: $model->number ?? '',
            orderId: $model->order_id,
            masterId: $model->master_id,
            status: $model->status ?? 'pending',
            description: $model->description,
            diagnosis: $model->diagnosis,
            workPerformed: $model->work_performed,
            notes: $model->notes,
            startedAt: $model->started_at,
            completedAt: $model->completed_at,
            estimatedCompletion: $model->estimated_completion,
            partsUsed: $model->parts_used ?? [],
            additionalData: $model->additional_data ?? [],
            workTimeMinutes: $model->work_time_minutes,
            price: $model->price,
            isDeleted: $model->trashed(),
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    public function toEloquent(Repair $repair): RepairModel
    {
        $model = new RepairModel();

        if ($repair->getId()) {
            $model = RepairModel::findOrNew($repair->getId());
        }

        $model->fill([
            'number' => $repair->getNumber(),
            'order_id' => $repair->getOrderId(),
            'master_id' => $repair->getMasterId(),
            'status' => $repair->getStatus(),
            'description' => $repair->getDescription(),
            'diagnosis' => $repair->getDiagnosis(),
            'work_performed' => $repair->getWorkPerformed(),
            'notes' => $repair->getNotes(),
            'started_at' => $repair->getStartedAt(),
            'completed_at' => $repair->getCompletedAt(),
            'estimated_completion' => $repair->getEstimatedCompletion(),
            'parts_used' => $repair->getPartsUsed(),
            'additional_data' => $repair->getAdditionalData(),
            'work_time_minutes' => $repair->getWorkTimeMinutes(),
            'price' => $repair->getPrice(),
        ]);

        return $model;
    }
}
