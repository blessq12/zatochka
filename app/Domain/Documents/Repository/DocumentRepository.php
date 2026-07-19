<?php

namespace App\Domain\Documents\Repository;

use App\Domain\Documents\Entity\Document;
use App\Domain\Documents\VO\DocumentType;

interface DocumentRepository
{
    public function getByType(DocumentType $type): Document;

    public function save(Document $document): void;

    /** @return list<Document> */
    public function all(): array;
}
