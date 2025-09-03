<?php

namespace App\Domain\Company\Interfaces;

use App\Domain\Company\Entities\Company;
use App\Domain\Company\ValueObjects\INN;

interface CompanyRepositoryInterface
{
    public function findById(int $id): ?Company;

    public function findByInn(INN $inn): ?Company;

    public function findActive(): array;

    public function findAll(): array;

    public function save(Company $company): void;

    public function delete(int $id): void;

    public function exists(int $id): bool;

    public function existsByInn(INN $inn): bool;
}
