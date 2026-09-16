<?php

namespace App\Application\SiteContent\Command;

use App\Domain\SiteContent\Repository\LegalDocumentRepository;

final readonly class UpdateLegalDocumentHandler
{
    public function __construct(
        private LegalDocumentRepository $documents,
    ) {}

    public function handle(UpdateLegalDocumentCommand $command): void
    {
        $document = $this->documents->getByType($command->type);
        $document->updateContent($command->title, $command->bodyHtml);
        $this->documents->save($document);
    }
}
