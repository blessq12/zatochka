<?php

namespace App\Application\SiteContent\Query;

use App\Application\SiteContent\DTO\LegalDocumentDTO;
use App\Domain\SiteContent\Repository\LegalDocumentRepository;
use App\Domain\SiteContent\VO\DocumentType;
use App\Shared\Domain\DomainException;

final readonly class GetLegalDocumentHandler
{
    public function __construct(
        private LegalDocumentRepository $documents,
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
