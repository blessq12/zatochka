<?php

namespace App\Domain\Company\Mapper;

use App\Domain\Company\Entity\User;
use App\Models\User as EloquentUser;

interface UserMapper
{
    public function toDomain(EloquentUser $eloquentModel): User;
    public function toEloquent(User $domainEntity): array;
    public function fromArray(array $data): User;
}
