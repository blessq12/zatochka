<?php

namespace App\Infrastructure\Company\Persistence\Repository;

use App\Domain\Company\Entity\Branch;
use App\Domain\Company\Repository\BranchRepositoryInterface;
use App\Infrastructure\Company\Persistence\Eloquent\BranchModel;
use App\Infrastructure\Company\Persistence\Mapper\BranchMapper;

final class EloquentBranchRepository implements BranchRepositoryInterface
{
    public function __construct(
        private BranchMapper $mapper,
    ) {}

    public function findById(int $id): ?Branch
    {
        $model = BranchModel::query()->find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findFirstActive(): ?Branch
    {
        $model = BranchModel::query()->where('is_active', true)->orderBy('id')->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(Branch $branch): Branch
    {
        $model = $branch->id() !== null
            ? BranchModel::query()->findOrFail($branch->id())
            : new BranchModel;

        $this->mapper->fillModel($branch, $model);
        $model->save();

        return $this->mapper->toDomain($model);
    }
}
