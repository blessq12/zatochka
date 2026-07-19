<?php

namespace App\Application\Documents\DTO;

final readonly class LegalDocumentDTO
{
    public function __construct(
        public string $type,
        public string $slug,
        public string $title,
        public string $bodyHtml,
        public string $updatedAt,
    ) {}

    /** @return array{type: string, slug: string, title: string, body_html: string, updated_at: string} */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'slug' => $this->slug,
            'title' => $this->title,
            'body_html' => $this->bodyHtml,
            'updated_at' => $this->updatedAt,
        ];
    }
}
