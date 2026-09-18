<?php

namespace App\Application\Order\Query;

use App\Application\Order\Support\DocumentTemplateVariableCatalog;
use App\Domain\Order\Repository\DocumentTemplateRepository;

final readonly class ListDocumentTemplatesHandler
{
    public function __construct(
        private DocumentTemplateRepository $templates,
    ) {}

    /**
     * @return array{templates: list<array{type: string, label: string, body: string, updated_at: string|null}>, variables: list<array{key: string, label: string, description: string}>, loops: list<array{key: string, label: string, fields: list<string>}>}
     */
    public function handle(): array
    {
        $templates = [];
        foreach ($this->templates->findAll() as $template) {
            $templates[] = [
                'type' => $template->type()->value,
                'label' => $template->type()->label(),
                'body' => $template->body(),
                'updated_at' => $template->updatedAt()?->format(DATE_ATOM),
            ];
        }

        return [
            'templates' => $templates,
            'variables' => DocumentTemplateVariableCatalog::all(),
            'loops' => DocumentTemplateVariableCatalog::loops(),
        ];
    }
}
