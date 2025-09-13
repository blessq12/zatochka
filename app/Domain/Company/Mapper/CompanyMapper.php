<?php

namespace App\Domain\Company\Mapper;

use App\Domain\Company\Entity\Company;
use App\Models\Company as EloquentCompany;

interface CompanyMapper
{
    public function toDomain(EloquentCompany $eloquentModel): Company;

    public function toEloquent(Company $domainEntity): array;

    public function fromArray(array $data): Company;
}
