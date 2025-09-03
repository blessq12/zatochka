<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Company\Entities\Branch;
use App\Domain\Company\Interfaces\BranchRepositoryInterface;
use App\Domain\Company\ValueObjects\BranchCode;
use App\Models\Branch as BranchModel;
use App\Infrastructure\Persistence\Mappers\BranchMapper;

class EloquentBranchRepository implements BranchRepositoryInterface
{
    public function __construct(
        private readonly BranchMapper $mapper
    ) {
    }

    public function findById(int $id): ?Branch
    {
        $model = BranchModel::find($id);
        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByCode(BranchCode $code): ?Branch
    {
        $model = BranchModel::where('code', $code->value())->first();
        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByCompanyId(int $companyId): array
    {
        $models = BranchModel::where('company_id', $companyId)->get();
        return array_map([$this->mapper, 'toDomain'], $models->all());
    }

    public function findActiveByCompanyId(int $companyId): array
    {
        $models = BranchModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('is_deleted', false)
            ->get();

        return array_map([$this->mapper, 'toDomain'], $models->all());
    }

    public function findMainByCompanyId(int $companyId): ?Branch
    {
        $model = BranchModel::where('company_id', $companyId)
            ->where('is_main', true)
            ->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findActive(): array
    {
        $models = BranchModel::where('is_active', true)
            ->where('is_deleted', false)
            ->get();

        return array_map([$this->mapper, 'toDomain'], $models->all());
    }

    public function findAll(): array
    {
        $models = BranchModel::all();
        return array_map([$this->mapper, 'toDomain'], $models->all());
    }

    public function save(Branch $branch): void
    {
        $model = $this->mapper->toEloquent($branch);
        $model->save();
    }

    public function delete(int $id): void
    {
        BranchModel::where('id', $id)->delete();
    }

    public function exists(int $id): bool
    {
        return BranchModel::where('id', $id)->exists();
    }

    public function existsByCode(BranchCode $code): bool
    {
        return BranchModel::where('code', $code->value())->exists();
    }

    public function countByCompanyId(int $companyId): int
    {
        return BranchModel::where('company_id', $companyId)->count();
    }
}
