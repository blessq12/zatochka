<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Order\Entity\DocumentTemplate;
use App\Domain\Order\Enum\DocumentType;
use App\Domain\Order\Repository\DocumentTemplateRepository;
use App\Infrastructure\Order\Eloquent\DocumentTemplateModel;
use App\Infrastructure\Order\Mapper\DocumentTemplateMapper;

final class EloquentDocumentTemplateRepository implements DocumentTemplateRepository
{
    public function __construct(
        private DocumentTemplateMapper $mapper,
    ) {}

    public function findAll(): array
    {
        return DocumentTemplateModel::query()
            ->orderBy('type')
            ->get()
            ->map(fn (DocumentTemplateModel $model): DocumentTemplate => $this->mapper->toDomain($model))
            ->all();
    }

    public function findByType(DocumentType $type): ?DocumentTemplate
    {
        $model = DocumentTemplateModel::query()
            ->where('type', $type->value)
            ->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(DocumentTemplate $template): DocumentTemplate
    {
        $model = DocumentTemplateModel::query()->updateOrCreate(
            ['type' => $template->type()->value],
            [
                'body' => $template->body(),
                'updated_by_identity_id' => $template->updatedByIdentityId(),
            ],
        );

        return $this->mapper->toDomain($model->fresh());
    }
}
