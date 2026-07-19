<?php

namespace App\Infrastructure\Documents\Repository;

use App\Domain\Documents\Entity\Document;
use App\Domain\Documents\Repository\DocumentRepository;
use App\Domain\Documents\VO\DocumentType;
use App\Infrastructure\Documents\Mapper\LegalDocumentMapper;
use App\Infrastructure\Documents\Model\LegalDocumentModel;
use App\Shared\Domain\DomainException;

final readonly class EloquentDocumentRepository implements DocumentRepository
{
    public function __construct(
        private LegalDocumentMapper $mapper,
    ) {}

    public function getByType(DocumentType $type): Document
    {
        $model = LegalDocumentModel::query()->find($type->value);

        if ($model === null) {
            throw new DomainException(sprintf('Legal document "%s" is not configured.', $type->value));
        }

        return $this->mapper->toDomain($model);
    }

    public function save(Document $document): void
    {
        $payload = $this->mapper->toPersistence($document);

        LegalDocumentModel::query()->updateOrCreate(
            ['type' => $payload['type']],
            $payload,
        );
    }

    public function all(): array
    {
        return LegalDocumentModel::query()
            ->orderBy('type')
            ->get()
            ->map(fn (LegalDocumentModel $model): Document => $this->mapper->toDomain($model))
            ->all();
    }
}
