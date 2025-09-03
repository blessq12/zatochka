<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Company\Entities\Branch;
use App\Domain\Company\Interfaces\BranchRepositoryInterface;
use App\Domain\Company\ValueObjects\BranchId;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\ValueObjects\BranchCode;
use App\Models\Branch as BranchModel;
use App\Infrastructure\Persistence\Mappers\BranchMapper;

class EloquentBranchRepository implements BranchRepositoryInterface
{
    public function __construct(
        private readonly BranchMapper $mapper
    ) {}

    public function findById(BranchId $id): ?Branch
    {
        $model = BranchModel::find($id->value());
        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByCode(BranchCode $code): ?Branch
    {
        $model = BranchModel::where('code', $code->value())->first();
        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        $models = BranchModel::where('company_id', $companyId->value())->get();
        return array_map([$this->mapper, 'toDomain'], $models->all());
    }

    public function findActiveByCompanyId(CompanyId $companyId): array
    {
        $models = BranchModel::where('company_id', $companyId->value())
            ->where('is_active', true)
            ->where('is_deleted', false)
            ->get();

        return array_map([$this->mapper, 'toDomain'], $models->all());
    }

    public function findMainByCompanyId(CompanyId $companyId): ?Branch
    {
        $model = BranchModel::where('company_id', $companyId->value())
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

    public function delete(BranchId $id): void
    {
        BranchModel::where('id', $id->value())->delete();
    }

    public function exists(BranchId $id): bool
    {
        return BranchModel::where('id', $id->value())->exists();
    }

    public function existsByCode(BranchCode $code): bool
    {
        return BranchModel::where('code', $code->value())->exists();
    }

    public function countByCompanyId(CompanyId $companyId): int
    {
        return BranchModel::where('company_id', $companyId->value())->count();
    }
}
