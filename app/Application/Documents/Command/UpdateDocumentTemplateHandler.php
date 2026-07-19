<?php

namespace App\Application\Documents\Command;

use App\Domain\Documents\Repository\DocumentTemplateRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class UpdateDocumentTemplateHandler
{
    public function __construct(
        private DocumentTemplateRepository $templates,
    ) {}

    public function handle(UpdateDocumentTemplateCommand $command): void
    {
        $template = $this->templates->findById(new EntityId((int) $command->templateId));

        if ($template === null) {
            throw new DomainException('Document template not found.');
        }

        $template->rename($command->name);
        $template->updateBody($command->bodyHtml);

        if ($command->isActive) {
            $template->activate();
        } else {
            $template->deactivate();
        }

        $this->templates->save($template);
    }
}
