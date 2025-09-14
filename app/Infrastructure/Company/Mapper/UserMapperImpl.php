<?php

namespace App\Infrastructure\Company\Mapper;

use App\Domain\Company\Mapper\UserMapper;
use App\Domain\Company\Entity\User;
use App\Models\User as EloquentUser;

class UserMapperImpl implements UserMapper
{
    public function toDomain(EloquentUser $eloquentModel): User
    {
        return new User(
            id: $eloquentModel->id,
            name: $eloquentModel->name,
            email: $eloquentModel->email,
            roles: $eloquentModel->getRoles(),
            isDeleted: $eloquentModel->is_deleted ?? false,
            emailVerifiedAt: $eloquentModel->email_verified_at?->toDateTime(),
            createdAt: $eloquentModel->created_at?->toDateTime(),
            updatedAt: $eloquentModel->updated_at?->toDateTime(),
        );
    }

    public function toEloquent(User $domainEntity): array
    {
        return [
            'id' => $domainEntity->getId(),
            'name' => $domainEntity->getName(),
            'email' => $domainEntity->getEmail(),
            'role' => $domainEntity->getRoles(),
            'is_deleted' => $domainEntity->isDeleted(),
            'email_verified_at' => $domainEntity->getEmailVerifiedAt(),
            'created_at' => $domainEntity->getCreatedAt(),
            'updated_at' => $domainEntity->getUpdatedAt(),
        ];
    }

    public function fromArray(array $data): User
    {
        return new User(
            id: $data['id'] ?? null,
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            roles: $data['role'] ?? [],
            isDeleted: $data['is_deleted'] ?? false,
            emailVerifiedAt: isset($data['email_verified_at'])
                ? ($data['email_verified_at'] instanceof \DateTime
                    ? $data['email_verified_at']
                    : new \DateTime($data['email_verified_at'])
                )
                : null,
            createdAt: isset($data['created_at'])
                ? ($data['created_at'] instanceof \DateTime
                    ? $data['created_at']
                    : new \DateTime($data['created_at'])
                )
                : null,
            updatedAt: isset($data['updated_at'])
                ? ($data['updated_at'] instanceof \DateTime
                    ? $data['updated_at']
                    : new \DateTime($data['updated_at'])
                )
                : null,
        );
    }
}
