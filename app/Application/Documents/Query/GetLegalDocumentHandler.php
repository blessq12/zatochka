<?php

namespace App\Application\Documents\Query;

use App\Application\Documents\DTO\LegalDocumentDTO;
use App\Domain\Documents\Repository\DocumentRepository;
use App\Domain\Documents\VO\DocumentType;
use App\Shared\Domain\DomainException;

final readonly class GetLegalDocumentHandler
{
    public function __construct(
        private DocumentRepository $documents,
    ) {}

    public function handle(string $publicSlug): LegalDocumentDTO
    {
        $type = DocumentType::fromPublicSlug($publicSlug);

        if ($type === null) {
            throw new DomainException('Legal document not found.');
        }

        $document = $this->documents->getByType($type);

        return new LegalDocumentDTO(
            type: $type->value,
            slug: $type->publicSlug(),
            title: $document->title(),
            bodyHtml: $document->bodyHtml(),
            updatedAt: $document->updatedAt()->format(DATE_ATOM),
        );
    }
}
