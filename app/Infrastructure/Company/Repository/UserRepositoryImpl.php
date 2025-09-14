<?php

namespace App\Infrastructure\Company\Repository;

use App\Domain\Company\Repository\UserRepository;
use App\Domain\Company\Entity\User;
use App\Domain\Company\Mapper\UserMapper;
use App\Models\User as EloquentUser;

class UserRepositoryImpl implements UserRepository
{

    public function __construct(
        private UserMapper $userMapper
    ) {}

    public function create(array $data): User
    {
        $model = EloquentUser::create($data);
        return $this->userMapper->toDomain($model);
    }

    public function get(int $id): ?User
    {
        $model = EloquentUser::find($id);

        if (!$model) {
            return null;
        }

        return $this->userMapper->toDomain($model);
    }

    public function update(User $user, array $data): User
    {
        $model = EloquentUser::findOrFail($user->getId());
        $model->update($data);

        return $this->userMapper->toDomain($model->fresh());
    }

    public function delete(int $id): bool
    {
        $model = EloquentUser::find($id);

        if (!$model) {
            return false;
        }

        return $model->update(['is_deleted' => true]);
    }

    public function exists(int $id): bool
    {
        return EloquentUser::where('id', $id)
            ->where('is_deleted', false)
            ->exists();
    }

    public function getAll(): array
    {
        return EloquentUser::where('is_deleted', false)
            ->get()
            ->map(fn($model) => $this->userMapper->toDomain($model))
            ->toArray();
    }
}
