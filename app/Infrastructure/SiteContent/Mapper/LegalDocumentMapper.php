<?php

namespace App\Infrastructure\SiteContent\Mapper;

use App\Domain\SiteContent\Entity\LegalDocument;
use App\Domain\SiteContent\VO\DocumentType;
use App\Infrastructure\SiteContent\Model\LegalDocumentModel;
use DateTimeImmutable;

final class LegalDocumentMapper
{
    public function toDomain(LegalDocumentModel $model): LegalDocument
    {
        return LegalDocument::reconstitute(
            DocumentType::from((string) $model->type),
            (string) $model->title,
            (string) $model->body_html,
            DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }

    /** @return array{type: string, title: string, body_html: string, updated_at: DateTimeImmutable} */
    public function toPersistence(LegalDocument $document): array
    {
        return [
            'type' => $document->type()->value,
            'title' => $document->title(),
            'body_html' => $document->bodyHtml(),
            'updated_at' => $document->updatedAt(),
        ];
    }
}
