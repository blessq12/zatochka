<?php

namespace App\Infrastructure\ClientPortal\Persistence\Repository;

use App\Domain\ClientPortal\Entity\SiteLead;
use App\Domain\ClientPortal\Repository\SiteLeadRepositoryInterface;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\SiteLeadModel;
use App\Infrastructure\ClientPortal\Persistence\Mapper\SiteLeadMapper;

final class EloquentSiteLeadRepository implements SiteLeadRepositoryInterface
{
    public function __construct(
        private SiteLeadMapper $mapper,
    ) {}

    public function findById(int $id): ?SiteLead
    {
        $model = SiteLeadModel::query()->find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(SiteLead $lead): SiteLead
    {
        $model = $lead->id() !== null
            ? SiteLeadModel::query()->findOrFail($lead->id())
            : new SiteLeadModel;

        $this->mapper->fillModel($lead, $model);
        $model->save();

        return $this->mapper->toDomain($model);
    }
}
