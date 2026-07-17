<?php

namespace App\Infrastructure\Identity\Repository;

use App\Domain\Identity\Entity\StaffUser;
use App\Domain\Identity\Repository\StaffUserRepository;
use App\Infrastructure\Identity\Mapper\StaffUserMapper;
use App\Models\User;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class EloquentStaffUserRepository implements StaffUserRepository
{
    public function __construct(
        private StaffUserMapper $mapper,
    ) {}

    public function save(StaffUser $user): void
    {
        $model = User::query()->find($user->id()->value);
        $model = $this->mapper->toPersistence($user, $model);
        $model->save();
    }

    public function findById(EntityId $id): ?StaffUser
    {
        $model = User::query()->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getById(EntityId $id): StaffUser
    {
        return $this->findById($id)
            ?? throw new DomainException(sprintf('Staff user %d not found.', $id->value));
    }

    public function emailExists(string $email, ?EntityId $exceptId = null): bool
    {
        $query = User::query()->where('email', strtolower(trim($email)));

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId->value);
        }

        return $query->exists();
    }
}
