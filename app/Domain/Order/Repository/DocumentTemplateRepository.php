<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Entity\DocumentTemplate;
use App\Domain\Order\Enum\DocumentType;

interface DocumentTemplateRepository
{
    /**
     * @return list<DocumentTemplate>
     */
    public function findAll(): array;

    public function findByType(DocumentType $type): ?DocumentTemplate;

    public function save(DocumentTemplate $template): DocumentTemplate;
}
