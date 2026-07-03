<?php

namespace App\Domain\OrderFulfillment\Repository;

use App\Domain\OrderFulfillment\Entity\DocumentTemplate;
use App\Domain\OrderFulfillment\Enum\DocumentType;

interface DocumentTemplateRepositoryInterface
{
    /** @return list<DocumentTemplate> */
    public function findAll(): array;

    public function findByType(DocumentType $type): ?DocumentTemplate;

    public function save(DocumentTemplate $template): DocumentTemplate;
}
