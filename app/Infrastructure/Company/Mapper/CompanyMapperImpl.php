<?php

namespace App\Infrastructure\Company\Mapper;

use App\Domain\Company\Entity\Company as CompanyEntity;
use App\Domain\Company\Mapper\CompanyMapper;
use App\Models\Company;

class CompanyMapperImpl implements CompanyMapper
{
    public function toDomain(Company $model): CompanyEntity
    {
        return new CompanyEntity(
            id: $model->id,
            name: $model->name,
            legalName: $model->legal_name,
            inn: $model->inn,
            kpp: $model->kpp,
            ogrn: $model->ogrn,
            legalAddress: $model->legal_address,
            description: $model->description,
            website: $model->website,
            phone: $model->phone,
            email: $model->email,
            bankName: $model->bank_name,
            bankBik: $model->bank_bik,
            bankAccount: $model->bank_account,
            bankCorAccount: $model->bank_cor_account,
            logoPath: $model->logo_path,
            additionalData: $model->additional_data ?? [],
            isActive: (bool) $model->is_active,
            isDeleted: (bool) $model->is_deleted,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }

    public function toEloquent(CompanyEntity $entity): array
    {
        return [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'legal_name' => $entity->getLegalName(),
            'inn' => $entity->getInn(),
            'kpp' => $entity->getKpp(),
            'ogrn' => $entity->getOgrn(),
            'legal_address' => $entity->getLegalAddress(),
            'description' => $entity->getDescription(),
            'website' => $entity->getWebsite(),
            'phone' => $entity->getPhone(),
            'email' => $entity->getEmail(),
            'bank_name' => $entity->getBankName(),
            'bank_bik' => $entity->getBankBik(),
            'bank_account' => $entity->getBankAccount(),
            'bank_cor_account' => $entity->getBankCorAccount(),
            'logo_path' => $entity->getLogoPath(),
            'additional_data' => $entity->getAdditionalData(),
            'is_active' => $entity->isActive(),
            'is_deleted' => $entity->isDeleted(),
            'created_at' => $entity->getCreatedAt(),
            'updated_at' => $entity->getUpdatedAt(),
        ];
    }

    public function fromArray(array $data): CompanyEntity
    {
        return new CompanyEntity(
            id: $data['id'] ?? null,
            name: $data['name'],
            legalName: $data['legal_name'],
            inn: $data['inn'],
            kpp: $data['kpp'] ?? null,
            ogrn: $data['ogrn'] ?? null,
            legalAddress: $data['legal_address'],
            description: $data['description'] ?? null,
            website: $data['website'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            bankName: $data['bank_name'] ?? null,
            bankBik: $data['bank_bik'] ?? null,
            bankAccount: $data['bank_account'] ?? null,
            bankCorAccount: $data['bank_cor_account'] ?? null,
            logoPath: $data['logo_path'] ?? null,
            additionalData: $data['additional_data'] ?? [],
            isActive: $data['is_active'] ?? true,
            isDeleted: $data['is_deleted'] ?? false,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}
