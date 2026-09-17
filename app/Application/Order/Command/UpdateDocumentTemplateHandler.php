<?php

namespace App\Application\Order\Command;

use App\Domain\Order\Entity\DocumentTemplate;
use App\Domain\Order\Enum\DocumentType;
use App\Domain\Order\Repository\DocumentTemplateRepository;

final readonly class UpdateDocumentTemplateHandler
{
    public function __construct(
        private DocumentTemplateRepository $templates,
    ) {}

    public function handle(DocumentType $type, string $body, ?int $identityId): DocumentTemplate
    {
        $existing = $this->templates->findByType($type);

        $template = ($existing ?? new DocumentTemplate(
            id: null,
            type: $type,
            body: '',
            updatedByIdentityId: null,
            updatedAt: null,
        ))->withBody($body, $identityId);

        return $this->templates->save($template);
    }
}
