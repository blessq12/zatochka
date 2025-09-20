<?php

namespace App\Infrastructure\Company\Mapper;

use App\Domain\Company\Entity\Branch;
use App\Domain\Company\Mapper\BranchMapper;
use App\Models\Branch as BranchModel;

class BranchMapperImpl implements BranchMapper
{
    public function toDomain(BranchModel $eloquentModel): Branch
    {
        return new Branch(
            id: $eloquentModel->id,
            companyId: $eloquentModel->company_id,
            name: $eloquentModel->name,
            code: $eloquentModel->code,
            address: $eloquentModel->address,
            phone: $eloquentModel->phone,
            email: $eloquentModel->email,
            workingHours: $eloquentModel->working_hours,
            workingSchedule: $eloquentModel->working_schedule,
            openingTime: $eloquentModel->opening_time,
            closingTime: $eloquentModel->closing_time,
            latitude: $eloquentModel->latitude,
            longitude: $eloquentModel->longitude,
            description: $eloquentModel->description,
            isActive: $eloquentModel->is_active,
            isMain: $eloquentModel->is_main,
            sortOrder: $eloquentModel->sort_order,
            isDeleted: $eloquentModel->is_deleted,
            createdAt: $eloquentModel->created_at?->toDateTime(),
            updatedAt: $eloquentModel->updated_at?->toDateTime(),
        );
    }

    public function toEloquent(Branch $domainEntity): array
    {
        return [
            'id' => $domainEntity->getId(),
            'company_id' => $domainEntity->getCompanyId(),
            'name' => $domainEntity->getName(),
            'code' => $domainEntity->getCode(),
            'address' => $domainEntity->getAddress(),
            'phone' => $domainEntity->getPhone(),
            'email' => $domainEntity->getEmail(),
            'working_hours' => $domainEntity->getWorkingHours(),
            'working_schedule' => $domainEntity->getWorkingSchedule(),
            'opening_time' => $domainEntity->getOpeningTime(),
            'closing_time' => $domainEntity->getClosingTime(),
            'latitude' => $domainEntity->getLatitude(),
            'longitude' => $domainEntity->getLongitude(),
            'description' => $domainEntity->getDescription(),
            'is_active' => $domainEntity->isActive(),
            'is_main' => $domainEntity->isMain(),
            'sort_order' => $domainEntity->getSortOrder(),
            'is_deleted' => $domainEntity->isDeleted(),
            'created_at' => $domainEntity->getCreatedAt(),
            'updated_at' => $domainEntity->getUpdatedAt(),
        ];
    }

    public function fromArray(array $data): Branch
    {
        return new Branch(
            id: $data['id'] ?? null,
            companyId: $data['company_id'],
            name: $data['name'],
            code: $data['code'],
            address: $data['address'],
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            workingHours: $data['working_hours'] ?? null,
            workingSchedule: $data['working_schedule'] ?? null,
            openingTime: $data['opening_time'] ?? null,
            closingTime: $data['closing_time'] ?? null,
            latitude: $data['latitude'] ?? null,
            longitude: $data['longitude'] ?? null,
            description: $data['description'] ?? null,
            isActive: $data['is_active'] ?? true,
            isMain: $data['is_main'] ?? false,
            sortOrder: $data['sort_order'] ?? 0,
            isDeleted: $data['is_deleted'] ?? false,
            createdAt: isset($data['created_at']) ? new \DateTime($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTime($data['updated_at']) : null,
        );
    }
}
