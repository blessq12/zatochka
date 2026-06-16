<?php

namespace App\Infrastructure\Persistence\Repositories\ClientPortal;

use App\Domain\ClientPortal\Entities\SiteLead;
use App\Domain\ClientPortal\Repositories\SiteLeadRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ClientPortal\SiteLeadModel;
use App\Infrastructure\Persistence\Mappers\ClientPortal\SiteLeadMapper;

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
