<?php

namespace App\Infrastructure\SiteContent\Mapper;

use App\Domain\SiteContent\Entity\CompanyProfile;
use App\Infrastructure\SiteContent\Model\CompanyProfileModel;
use App\Shared\ValueObject\EntityId;

final class CompanyProfileMapper
{
    public function toDomain(CompanyProfileModel $model): CompanyProfile
    {
        return CompanyProfile::reconstitute(
            new EntityId((int) $model->id),
            (string) $model->owner_name,
            (string) $model->inn,
            (string) $model->ogrn,
            (string) $model->legal_address,
            (string) $model->actual_address,
        );
    }

    /** @return array<string, mixed> */
    public function toPersistence(CompanyProfile $profile): array
    {
        return [
            'id' => $profile->id()->value,
            'owner_name' => $profile->ownerName(),
            'inn' => $profile->inn(),
            'ogrn' => $profile->ogrn(),
            'legal_address' => $profile->legalAddress(),
            'actual_address' => $profile->actualAddress(),
        ];
    }
}
