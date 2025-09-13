<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Company\Entity\Company as CompanyEntity;
use App\Domain\Company\Mapper\CompanyMapper;
use App\Domain\Company\Repository\CompanyRepository;
use App\Models\Company;

class CompanyRepositoryImpl implements CompanyRepository
{
    public function __construct(
        private CompanyMapper $companyMapper
    ) {}

    public function create(array $data): CompanyEntity
    {
        $model = Company::create($data);
        return $this->companyMapper->toDomain($model);
    }

    public function get(int $id): ?CompanyEntity
    {
        $model = Company::find($id);
        return $model ? $this->companyMapper->toDomain($model) : null;
    }

    public function update(CompanyEntity $company, array $data): CompanyEntity
    {
        $model = Company::find($company->getId());
        $model->update($data);
        return $this->companyMapper->toDomain($model->fresh());
    }

    public function delete(int $id): bool
    {
        return Company::where('id', $id)->update(['is_deleted' => true]) > 0;
    }

    public function exists(int $id): bool
    {
        return Company::where('id', $id)->exists();
    }

    public function getAll(): array
    {
        $models = Company::where('is_deleted', false)->get();
        return $models->map(fn($model) => $this->companyMapper->toDomain($model))->toArray();
    }

    public function getActive(): array
    {
        $models = Company::where('is_deleted', false)
            ->where('is_active', true)
            ->get();

        return $models->map(fn($model) => $this->companyMapper->toDomain($model))->toArray();
    }

    public function getMainCompany(): ?CompanyEntity
    {
        $model = Company::where('is_deleted', false)
            ->where('is_active', true)
            ->whereJsonContains('additional_data->is_main', true)
            ->first();

        return $model ? $this->companyMapper->toDomain($model) : null;
    }

    public function existsByInn(string $inn, ?int $excludeId = null): bool
    {
        $query = Company::where('inn', $inn)->where('is_deleted', false);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        $query = Company::where('name', $name)->where('is_deleted', false);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
