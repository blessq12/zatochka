<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Company\Entities\Branch;
use App\Domain\Company\ValueObjects\BranchCode;
use App\Domain\Company\ValueObjects\WorkingSchedule;
use App\Models\Branch as BranchModel;

class BranchMapper
{
    public function toDomain(BranchModel $model): Branch
    {
        // Обрабатываем working_schedule - если это JSON строка, декодируем в массив
        $workingScheduleData = [];
        if ($model->working_schedule) {
            if (is_string($model->working_schedule)) {
                $decoded = json_decode($model->working_schedule, true);
                $workingScheduleData = is_array($decoded) ? $decoded : [];
            } elseif (is_array($model->working_schedule)) {
                $workingScheduleData = $model->working_schedule;
            }
        }

        $workingSchedule = !empty($workingScheduleData)
            ? WorkingSchedule::fromArray($workingScheduleData)
            : WorkingSchedule::createDefault();

        return Branch::reconstitute(
            $model->id,
            $model->company_id,
            $model->name,
            BranchCode::fromString($model->code),
            $model->address,
            $model->phone,
            $model->email,
            $workingSchedule,
            $model->opening_time,
            $model->closing_time,
            $model->latitude,
            $model->longitude,
            $model->description,
            $this->parseAdditionalData($model->additional_data),
            $model->is_active,
            $model->is_main,
            $model->sort_order,
            $model->is_deleted,
            \DateTimeImmutable::createFromInterface($model->created_at),
            \DateTimeImmutable::createFromInterface($model->updated_at)
        );
    }

    private function parseAdditionalData($additionalData): array
    {
        if (empty($additionalData)) {
            return [];
        }

        if (is_string($additionalData)) {
            $decoded = json_decode($additionalData, true);
            return is_array($decoded) ? $decoded : [];
        }

        if (is_array($additionalData)) {
            return $additionalData;
        }

        return [];
    }

    public function toEloquent(Branch $branch): BranchModel
    {
        $model = new BranchModel();

        // Если ID больше 0, значит это существующий филиал
        if ($branch->id() > 0) {
            $model->id = $branch->id();
            $model->exists = true; // Указываем Laravel, что это существующая запись
        }
        // Если ID = 0, Laravel сам сгенерирует автоинкремент

        $model->company_id = $branch->companyId();
        $model->name = $branch->name();
        $model->code = $branch->code()->value();
        $model->address = $branch->address();
        $model->phone = $branch->phone();
        $model->email = $branch->email();

        // Сохраняем working_schedule как JSON строку
        $workingSchedule = $branch->workingSchedule();
        $model->working_schedule = $workingSchedule ? json_encode($workingSchedule->toArray()) : null;

        $model->opening_time = $branch->openingTime();
        $model->closing_time = $branch->closingTime();
        $model->latitude = $branch->latitude();
        $model->longitude = $branch->longitude();
        $model->description = $branch->description();
        $model->additional_data = $branch->additionalData();
        $model->is_active = $branch->isActive();
        $model->is_main = $branch->isMain();
        $model->sort_order = $branch->sortOrder();
        $model->is_deleted = $branch->isDeleted();
        $model->created_at = $branch->createdAt();
        $model->updated_at = $branch->updatedAt();

        return $model;
    }
}
