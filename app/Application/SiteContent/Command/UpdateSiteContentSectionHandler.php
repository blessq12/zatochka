<?php

namespace App\Application\SiteContent\Command;

use App\Domain\SiteContent\Repository\SiteContentRepository;
use App\Shared\Domain\DomainException;

final readonly class UpdateSiteContentSectionHandler
{
    public function __construct(
        private SiteContentRepository $siteContent,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function handle(string $section, array $payload): array
    {
        return match ($section) {
            'company' => $this->saveCompany($payload),
            'contacts' => $this->saveContacts($payload),
            'schedule' => $this->saveSchedule($payload),
            'faq' => $this->saveFaq($payload),
            'delivery' => $this->saveDelivery($payload),
            'prices' => $this->savePrices($payload),
            default => throw new DomainException('Unknown site content section.'),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function saveCompany(array $payload): array
    {
        $this->siteContent->saveCompany($payload);

        return (array) ($this->siteContent->bootstrap()['company'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function saveContacts(array $payload): array
    {
        $this->siteContent->saveContacts($payload);

        return (array) ($this->siteContent->bootstrap()['contacts'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function saveSchedule(array $payload): array
    {
        $days = array_values((array) ($payload['days'] ?? $payload));
        $this->siteContent->saveSchedule($days);

        return (array) ($this->siteContent->bootstrap()['schedule'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function saveFaq(array $payload): array
    {
        $items = array_values((array) ($payload['items'] ?? $payload));
        $this->siteContent->saveFaq($items);

        return (array) ($this->siteContent->bootstrap()['faq'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function saveDelivery(array $payload): array
    {
        $this->siteContent->saveDelivery($payload);

        return (array) ($this->siteContent->bootstrap()['delivery_info'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function savePrices(array $payload): array
    {
        $items = array_values((array) ($payload['items'] ?? $payload));
        $this->siteContent->savePrices($items);

        return ['prices' => (array) ($this->siteContent->bootstrap()['prices'] ?? [])];
    }
}
