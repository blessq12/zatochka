<?php

namespace App\Infrastructure\Company\Persistence\Mapper;

use App\Domain\Company\Entity\SiteContent;
use App\Infrastructure\Company\Persistence\Eloquent\SiteContentModel;

final class SiteContentMapper
{
    public function toDomain(SiteContentModel $model): SiteContent
    {
        return new SiteContent(
            id: $model->id,
            key: $model->key,
            value: $model->value ?? [],
        );
    }
}
