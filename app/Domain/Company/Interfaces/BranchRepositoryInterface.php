<?php

namespace App\Domain\Company\Interfaces;

use App\Domain\Company\Entities\Branch;
use App\Domain\Company\ValueObjects\BranchCode;

interface BranchRepositoryInterface
{
    public function findById(int $id): ?Branch;

    public function findByCode(BranchCode $code): ?Branch;

    public function findByCompanyId(int $companyId): array;

    public function findActiveByCompanyId(int $companyId): array;

    public function findMainByCompanyId(int $companyId): ?Branch;

    public function findActive(): array;

    public function findAll(): array;

    public function save(Branch $branch): void;

    public function delete(int $id): void;

    public function exists(int $id): bool;

    public function existsByCode(BranchCode $code): bool;

    public function countByCompanyId(int $companyId): int;
}
