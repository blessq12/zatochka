<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Entity\DocumentTemplate;
use App\Domain\Order\VO\PdfTemplateKind;
use App\Shared\ValueObject\EntityId;

interface DocumentTemplateRepository
{
    public function findById(EntityId $id): ?DocumentTemplate;

    public function getByKind(PdfTemplateKind $kind): DocumentTemplate;

    public function save(DocumentTemplate $template): void;
}
