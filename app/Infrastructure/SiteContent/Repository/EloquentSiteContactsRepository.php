<?php

namespace App\Infrastructure\SiteContent\Repository;

use App\Domain\SiteContent\Entity\SiteContacts;
use App\Domain\SiteContent\Repository\SiteContactsRepository;
use App\Infrastructure\SiteContent\Mapper\SiteContactsMapper;
use App\Infrastructure\SiteContent\Model\SiteContactsModel;
use App\Shared\Domain\DomainException;

final readonly class EloquentSiteContactsRepository implements SiteContactsRepository
{
    public function __construct(
        private SiteContactsMapper $mapper,
    ) {}

    public function get(): SiteContacts
    {
        $model = SiteContactsModel::query()->find(SiteContacts::SINGLETON_ID);

        if ($model === null) {
            throw new DomainException('Site contacts are not configured.');
        }

        return $this->mapper->toDomain($model);
    }

    public function save(SiteContacts $contacts): void
    {
        $payload = $this->mapper->toPersistence($contacts);

        SiteContactsModel::query()->updateOrCreate(
            ['id' => $payload['id']],
            $payload,
        );
    }
}
