<?php

namespace App\Domain\Company\Interfaces;

use App\Domain\Company\Entities\Branch;
use App\Domain\Company\ValueObjects\BranchId;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\ValueObjects\BranchCode;

interface BranchRepositoryInterface
{
    public function findById(BranchId $id): ?Branch;
    
    public function findByCode(BranchCode $code): ?Branch;
    
    public function findByCompanyId(CompanyId $companyId): array;
    
    public function findActiveByCompanyId(CompanyId $companyId): array;
    
    public function findMainByCompanyId(CompanyId $companyId): ?Branch;
    
    public function findActive(): array;
    
    public function findAll(): array;
    
    public function save(Branch $branch): void;
    
    public function delete(BranchId $id): void;
    
    public function exists(BranchId $id): bool;
    
    public function existsByCode(BranchCode $code): bool;
    
    public function countByCompanyId(CompanyId $companyId): int;
}
