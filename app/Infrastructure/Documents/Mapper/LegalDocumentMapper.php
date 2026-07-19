<?php

namespace App\Infrastructure\Documents\Mapper;

use App\Domain\Documents\Entity\Document;
use App\Domain\Documents\VO\DocumentType;
use App\Infrastructure\Documents\Model\LegalDocumentModel;
use DateTimeImmutable;

final class LegalDocumentMapper
{
    public function toDomain(LegalDocumentModel $model): Document
    {
        return Document::reconstitute(
            DocumentType::from((string) $model->type),
            (string) $model->title,
            (string) $model->body_html,
            DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }

    /** @return array{type: string, title: string, body_html: string, updated_at: DateTimeImmutable} */
    public function toPersistence(Document $document): array
    {
        return [
            'type' => $document->type()->value,
            'title' => $document->title(),
            'body_html' => $document->bodyHtml(),
            'updated_at' => $document->updatedAt(),
        ];
    }
}
