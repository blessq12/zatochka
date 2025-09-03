<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Company\Entities\Company;
use App\Domain\Company\Interfaces\CompanyRepositoryInterface;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\ValueObjects\CompanyName;
use App\Domain\Company\ValueObjects\LegalName;
use App\Domain\Company\ValueObjects\INN;
use App\Models\Company as CompanyModel;
use App\Infrastructure\Persistence\Mappers\CompanyMapper;

class EloquentCompanyRepository implements CompanyRepositoryInterface
{
    public function __construct(
        private readonly CompanyMapper $mapper
    ) {}

    public function findById(CompanyId $id): ?Company
    {
        $model = CompanyModel::find($id->value());
        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByInn(INN $inn): ?Company
    {
        $model = CompanyModel::where('inn', $inn->value())->first();
        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findActive(): array
    {
        $models = CompanyModel::where('is_active', true)
            ->where('is_deleted', false)
            ->get();

        return array_map([$this->mapper, 'toDomain'], $models->all());
    }

    public function findAll(): array
    {
        $models = CompanyModel::all();
        return array_map([$this->mapper, 'toDomain'], $models->all());
    }

        public function save(Company $company): void
    {
        $model = $this->mapper->toEloquent($company);
        $model->save();
    }

    public function delete(CompanyId $id): void
    {
        CompanyModel::where('id', $id->value())->delete();
    }

    public function exists(CompanyId $id): bool
    {
        return CompanyModel::where('id', $id->value())->exists();
    }

    public function existsByInn(INN $inn): bool
    {
        return CompanyModel::where('inn', $inn->value())->exists();
    }
}
