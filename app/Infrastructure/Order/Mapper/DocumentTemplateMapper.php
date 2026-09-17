<?php

namespace App\Infrastructure\Order\Mapper;

use App\Domain\Order\Entity\DocumentTemplate;
use App\Domain\Order\Enum\DocumentType;
use App\Infrastructure\Order\Eloquent\DocumentTemplateModel;

final class DocumentTemplateMapper
{
    public function toDomain(DocumentTemplateModel $model): DocumentTemplate
    {
        return new DocumentTemplate(
            id: (int) $model->id,
            type: DocumentType::from((string) $model->type),
            body: (string) $model->body,
            updatedByIdentityId: $model->updated_by_identity_id !== null
                ? (int) $model->updated_by_identity_id
                : null,
            updatedAt: $model->updated_at?->toDateTimeImmutable(),
        );
    }
}
