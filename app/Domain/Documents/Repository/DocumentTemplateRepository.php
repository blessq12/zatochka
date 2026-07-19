<?php

namespace App\Domain\Documents\Repository;

use App\Domain\Documents\Entity\DocumentTemplate;
use App\Domain\Documents\VO\PdfTemplateKind;
use App\Shared\ValueObject\EntityId;

interface DocumentTemplateRepository
{
    public function findById(EntityId $id): ?DocumentTemplate;

    public function getByKind(PdfTemplateKind $kind): DocumentTemplate;

    public function save(DocumentTemplate $template): void;
}
