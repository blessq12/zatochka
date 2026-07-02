<?php

namespace App\Infrastructure\OrderFulfillment\Persistence\Mapper;

use App\Domain\OrderFulfillment\Entity\DocumentTemplate;
use App\Domain\OrderFulfillment\Enum\DocumentType;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\DocumentTemplateModel;

final class DocumentTemplateMapper
{
    public function toDomain(DocumentTemplateModel $model): DocumentTemplate
    {
        return new DocumentTemplate(
            id: $model->id,
            type: DocumentType::from($model->type),
            body: $model->body,
            updatedByUserId: $model->updated_by_user_id,
            updatedAt: $model->updated_at?->toDateTimeImmutable(),
        );
    }
}
