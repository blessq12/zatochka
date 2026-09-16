<?php

namespace App\Domain\SiteContent\Repository;

use App\Domain\SiteContent\Entity\LegalDocument;
use App\Domain\SiteContent\VO\DocumentType;

interface LegalDocumentRepository
{
    public function getByType(DocumentType $type): LegalDocument;

    public function save(LegalDocument $document): void;

    /** @return list<LegalDocument> */
    public function all(): array;
}
