<?php

namespace App\Domain\Company\Repository;

use App\Domain\Company\Entity\Company;

interface CompanyRepository
{
    public function create(array $data): Company;

    public function get(int $id): ?Company;

    public function update(Company $company, array $data): Company;

    public function delete(int $id): bool;

    public function exists(int $id): bool;

    public function getAll(): array;

    public function getActive(): array;

    public function getMainCompany(): ?Company;

    public function existsByInn(string $inn, ?int $excludeId = null): bool;

    public function existsByName(string $name, ?int $excludeId = null): bool;
}
