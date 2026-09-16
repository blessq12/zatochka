<?php

namespace App\Domain\SiteContent\Repository;

interface SiteContentRepository
{
    /**
     * Сырые данные контента сайта (без presentation-shape).
     *
     * @return array<string, mixed>
     */
    public function bootstrap(): array;

    /**
     * @return array{type: string, slug: string, title: string, body_html: string, updated_at: string}|null
     */
    public function legalDocumentBySlug(string $slug): ?array;
}
