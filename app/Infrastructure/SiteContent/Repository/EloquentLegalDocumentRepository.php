<?php

namespace App\Infrastructure\SiteContent\Repository;

use App\Domain\SiteContent\Entity\LegalDocument;
use App\Domain\SiteContent\Repository\LegalDocumentRepository;
use App\Domain\SiteContent\VO\DocumentType;
use App\Infrastructure\SiteContent\Mapper\LegalDocumentMapper;
use App\Infrastructure\SiteContent\Model\LegalDocumentModel;
use App\Shared\Domain\DomainException;

final readonly class EloquentLegalDocumentRepository implements LegalDocumentRepository
{
    public function __construct(
        private LegalDocumentMapper $mapper,
    ) {}

    public function getByType(DocumentType $type): LegalDocument
    {
        $model = LegalDocumentModel::query()->find($type->value);

        if ($model === null) {
            throw new DomainException(sprintf('Legal document "%s" is not configured.', $type->value));
        }

        return $this->mapper->toDomain($model);
    }

    public function save(LegalDocument $document): void
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
            ->map(fn (LegalDocumentModel $model): LegalDocument => $this->mapper->toDomain($model))
            ->all();
    }
}
