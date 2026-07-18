<?php

namespace App\Infrastructure\SiteContent\Repository;

use App\Domain\SiteContent\Entity\CompanyProfile;
use App\Domain\SiteContent\Repository\CompanyProfileRepository;
use App\Infrastructure\SiteContent\Mapper\CompanyProfileMapper;
use App\Infrastructure\SiteContent\Model\CompanyProfileModel;
use App\Shared\Domain\DomainException;

final readonly class EloquentCompanyProfileRepository implements CompanyProfileRepository
{
    public function __construct(
        private CompanyProfileMapper $mapper,
    ) {}

    public function get(): CompanyProfile
    {
        $model = CompanyProfileModel::query()->find(CompanyProfile::SINGLETON_ID);

        if ($model === null) {
            throw new DomainException('Company profile is not configured.');
        }

        return $this->mapper->toDomain($model);
    }

    public function save(CompanyProfile $profile): void
    {
        $payload = $this->mapper->toPersistence($profile);

        CompanyProfileModel::query()->updateOrCreate(
            ['id' => $payload['id']],
            $payload,
        );
    }
}
