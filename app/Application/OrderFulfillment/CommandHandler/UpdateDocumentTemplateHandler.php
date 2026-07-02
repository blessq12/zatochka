<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\UpdateDocumentTemplateCommand;
use App\Domain\OrderFulfillment\Entity\DocumentTemplate;
use App\Domain\OrderFulfillment\Event\DocumentTemplateUpdated;
use App\Domain\OrderFulfillment\Repository\DocumentTemplateRepositoryInterface;

final class UpdateDocumentTemplateHandler
{
    public function __construct(
        private DocumentTemplateRepositoryInterface $templates,
    ) {}

    public function handle(UpdateDocumentTemplateCommand $command): DocumentTemplate
    {
        $existing = $this->templates->findByType($command->type);

        $template = ($existing ?? new DocumentTemplate(
            id: null,
            type: $command->type,
            body: '',
            updatedByUserId: null,
            updatedAt: null,
        ))->withBody($command->body, $command->userId);

        $saved = $this->templates->save($template);

        event(new DocumentTemplateUpdated(
            type: $command->type,
            userId: $command->userId,
        ));

        return $saved;
    }
}
