<?php

namespace App\Application\Documents\Command;

use App\Domain\Documents\Repository\DocumentRepository;

final readonly class UpdateLegalDocumentHandler
{
    public function __construct(
        private DocumentRepository $documents,
    ) {}

    public function handle(UpdateLegalDocumentCommand $command): void
    {
        $document = $this->documents->getByType($command->type);
        $document->updateContent($command->title, $command->bodyHtml);
        $this->documents->save($document);
    }
}
