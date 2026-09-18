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

    /**
     * @return list<array{type: string, slug: string, title: string, body_html: string, updated_at: string}>
     */
    public function legalDocuments(): array;

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveCompany(array $data): void;

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveContacts(array $data): void;

    /**
     * @param  list<array{label: string, hours: string}>  $days
     */
    public function saveSchedule(array $days): void;

    /**
     * @param  list<array{question: string, answer_lines: list<string>}>  $items
     */
    public function saveFaq(array $items): void;

    /**
     * @param  array{free_conditions?: list<string>, advantages?: list<array{title: string, text: string}>}  $data
     */
    public function saveDelivery(array $data): void;

    /**
     * @param  list<array{category: string, name: string, description?: string|null, price: string, prefix?: string|null}>  $items
     */
    public function savePrices(array $items): void;

    /**
     * @param  array{slug: string, type: string, title: string, body_html: string}  $data
     * @return array{type: string, slug: string, title: string, body_html: string, updated_at: string}
     */
    public function saveLegalDocument(array $data): array;

    public function deleteLegalDocument(string $slug): bool;
}
