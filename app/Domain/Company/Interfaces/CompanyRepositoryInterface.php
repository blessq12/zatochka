<?php

namespace App\Domain\Company\Interfaces;

use App\Domain\Company\Entities\Company;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\ValueObjects\INN;

interface CompanyRepositoryInterface
{
    public function findById(CompanyId $id): ?Company;
    
    public function findByInn(INN $inn): ?Company;
    
    public function findActive(): array;
    
    public function findAll(): array;
    
    public function save(Company $company): void;
    
    public function delete(CompanyId $id): void;
    
    public function exists(CompanyId $id): bool;
    
    public function existsByInn(INN $inn): bool;
}
