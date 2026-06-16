<?php

namespace App\Infrastructure\Persistence\Repositories\Catalog;

use App\Domain\Catalog\Entities\Branch;
use App\Domain\Catalog\Repositories\BranchRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Catalog\BranchModel;
use App\Infrastructure\Persistence\Mappers\Catalog\BranchMapper;

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
