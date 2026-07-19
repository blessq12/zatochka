<?php

namespace App\Infrastructure\Documents\Mapper;

use App\Domain\Documents\Entity\DocumentTemplate;
use App\Domain\Documents\VO\PdfTemplateKind;
use App\Infrastructure\Documents\Model\DocumentTemplateModel;
use App\Shared\ValueObject\EntityId;

final class DocumentTemplateMapper
{
    public function toDomain(DocumentTemplateModel $model): DocumentTemplate
    {
        return DocumentTemplate::reconstitute(
            new EntityId((int) $model->id),
            PdfTemplateKind::from((string) $model->kind),
            (string) $model->name,
            (string) $model->body_html,
            (bool) $model->is_active,
        );
    }

    /** @return array{id: int, kind: string, name: string, body_html: string, is_active: bool} */
    public function toPersistence(DocumentTemplate $template): array
    {
        return [
            'id' => $template->id()->value,
            'kind' => $template->kind()->value,
            'name' => $template->name(),
            'body_html' => $template->bodyHtml(),
            'is_active' => $template->isActive(),
        ];
    }
}
