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
        $workingSchedule = $model->working_schedule
            ? WorkingSchedule::fromArray($model->working_schedule)
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
            $model->additional_data ?? [],
            $model->is_active,
            $model->is_main,
            $model->sort_order,
            $model->is_deleted,
            \DateTimeImmutable::createFromInterface($model->created_at),
            \DateTimeImmutable::createFromInterface($model->updated_at)
        );
    }

    public function toEloquent(Branch $branch): BranchModel
    {
        $model = new BranchModel();

        // Если ID больше 0, значит это существующий филиал
        if ($branch->id() > 0) {
            $model->id = $branch->id();
        }
        // Если ID = 0, Laravel сам сгенерирует автоинкремент

        $model->company_id = $branch->companyId();
        $model->name = $branch->name();
        $model->code = $branch->code()->value();
        $model->address = $branch->address();
        $model->phone = $branch->phone();
        $model->email = $branch->email();
        $model->working_schedule = $branch->workingSchedule()->toArray();
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
