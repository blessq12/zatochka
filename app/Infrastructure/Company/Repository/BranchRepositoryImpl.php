<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Company\Entity\Branch;
use App\Domain\Company\Repository\BranchRepository;
use App\Domain\Company\Mapper\BranchMapper;
use App\Models\Branch as BranchModel;

class BranchRepositoryImpl implements BranchRepository
{
    public function __construct(
        private BranchMapper $branchMapper
    ) {}

    public function create(array $data): Branch
    {
        $model = BranchModel::create($data);
        return $this->branchMapper->toDomain($model);
    }

    public function get(int $id): ?Branch
    {
        $model = BranchModel::find($id);
        return $model ? $this->branchMapper->toDomain($model) : null;
    }

    public function update(Branch $branch, array $data): Branch
    {
        $model = BranchModel::findOrFail($branch->getId());
        $model->update($data);
        return $this->branchMapper->toDomain($model->fresh());
    }

    public function delete(int $id): bool
    {
        return BranchModel::where('id', $id)->update(['is_deleted' => true]) > 0;
    }

    public function exists(int $id): bool
    {
        return BranchModel::where('id', $id)->where('is_deleted', false)->exists();
    }

    public function getAll(): array
    {
        $models = BranchModel::where('is_deleted', false)->get();
        return $models->map(fn($model) => $this->branchMapper->toDomain($model))->toArray();
    }

    public function getMain(): ?Branch
    {
        $model = BranchModel::where('is_deleted', false)
            ->where('is_main', true)
            ->first();

        return $model ? $this->branchMapper->toDomain($model) : null;
    }
}
