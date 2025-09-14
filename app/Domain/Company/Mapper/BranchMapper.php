<?php

namespace App\Domain\Company\Mapper;

use App\Domain\Company\Entity\Branch;
use App\Models\Branch as EloquentBranch;

interface BranchMapper
{
    public function toDomain(EloquentBranch $eloquentModel): Branch;

    public function toEloquent(Branch $domainEntity): array;

    public function fromArray(array $data): Branch;
}
