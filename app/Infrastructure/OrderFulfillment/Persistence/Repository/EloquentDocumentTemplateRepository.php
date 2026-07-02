<?php

namespace App\Infrastructure\OrderFulfillment\Persistence\Repository;

use App\Domain\OrderFulfillment\Entity\DocumentTemplate;
use App\Domain\OrderFulfillment\Enum\DocumentType;
use App\Domain\OrderFulfillment\Repository\DocumentTemplateRepositoryInterface;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\DocumentTemplateModel;
use App\Infrastructure\OrderFulfillment\Persistence\Mapper\DocumentTemplateMapper;

final class EloquentDocumentTemplateRepository implements DocumentTemplateRepositoryInterface
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
                'updated_by_user_id' => $template->updatedByUserId(),
            ],
        );

        return $this->mapper->toDomain($model->fresh());
    }
}
