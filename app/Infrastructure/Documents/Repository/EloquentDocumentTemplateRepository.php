<?php

namespace App\Infrastructure\Documents\Repository;

use App\Domain\Documents\Entity\DocumentTemplate;
use App\Domain\Documents\Repository\DocumentTemplateRepository;
use App\Domain\Documents\VO\PdfTemplateKind;
use App\Infrastructure\Documents\Mapper\DocumentTemplateMapper;
use App\Infrastructure\Documents\Model\DocumentTemplateModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class EloquentDocumentTemplateRepository implements DocumentTemplateRepository
{
    public function __construct(
        private DocumentTemplateMapper $mapper,
    ) {}

    public function findById(EntityId $id): ?DocumentTemplate
    {
        $model = DocumentTemplateModel::query()->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getByKind(PdfTemplateKind $kind): DocumentTemplate
    {
        $model = DocumentTemplateModel::query()->where('kind', $kind->value)->first();

        if ($model === null) {
            throw new DomainException(sprintf('Document template "%s" is not configured.', $kind->value));
        }

        return $this->mapper->toDomain($model);
    }

    public function save(DocumentTemplate $template): void
    {
        $payload = $this->mapper->toPersistence($template);

        DocumentTemplateModel::query()->updateOrCreate(
            ['id' => $payload['id']],
            $payload,
        );
    }
}
