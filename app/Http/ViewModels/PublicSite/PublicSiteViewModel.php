<?php

namespace App\Http\ViewModels\PublicSite;

final readonly class PublicSiteViewModel
{
    /**
     * @param  array<string, mixed>  $bootstrap
     * @param  array{average_rating: ?string, items: list<array<string, mixed>>}|null  $reviews
     * @param  array{type: string, slug: string, title: string, body_html: string, updated_at: string}|null  $document
     */
    public function __construct(
        public array $bootstrap,
        public string $title,
        public string $currentPath,
        public ?array $reviews = null,
        public ?array $document = null,
    ) {}

    /** @return array<string, mixed> */
    public function company(): array
    {
        return $this->bootstrap['company'] ?? [];
    }

    /** @return array<string, mixed> */
    public function contacts(): array
    {
        return $this->bootstrap['contacts'] ?? [];
    }

    public function phone(): string
    {
        return (string) ($this->contacts()['phone'] ?? '');
    }

    public function phoneTel(): string
    {
        $digits = preg_replace('/\D+/', '', $this->phone()) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '8') && strlen($digits) === 11) {
            return '+7'.substr($digits, 1);
        }

        if (str_starts_with($digits, '7')) {
            return '+'.$digits;
        }

        return '+'.$digits;
    }

    /** @return list<array<string, mixed>> */
    public function socialLinks(): array
    {
        return array_values((array) ($this->contacts()['social']['links'] ?? []));
    }

    /** @return list<array<string, mixed>> */
    public function scheduleDays(): array
    {
        return array_values((array) ($this->bootstrap['schedule']['days'] ?? []));
    }

    /** @return list<array<string, mixed>> */
    public function faqItems(): array
    {
        return array_values((array) ($this->bootstrap['faq']['items'] ?? []));
    }

    /** @return array<string, mixed> */
    public function deliveryInfo(): array
    {
        return $this->bootstrap['delivery_info'] ?? [];
    }

    /** @return list<array<string, mixed>> */
    public function prices(): array
    {
        return array_values((array) ($this->bootstrap['prices'] ?? []));
    }

    /** @return list<array<string, mixed>> */
    public function sharpeningPrices(): array
    {
        return array_values(array_filter(
            $this->prices(),
            static fn (array $item): bool => ($item['category'] ?? '') === 'sharpening',
        ));
    }

    /** @return list<array<string, mixed>> */
    public function repairPrices(): array
    {
        return array_values(array_filter(
            $this->prices(),
            static fn (array $item): bool => ($item['category'] ?? '') === 'repair',
        ));
    }

    public function formatPrice(array $item): string
    {
        $price = ($item['price'] ?? '').'₽';
        $prefix = $item['prefix'] ?? null;

        return match ($prefix) {
            'from' => 'от '.$price,
            'to' => 'до '.$price,
            default => $price,
        };
    }

    public function isActive(string $path): bool
    {
        $current = rtrim($this->currentPath, '/') ?: '/';
        $target = rtrim($path, '/') ?: '/';

        return $current === $target;
    }

    public function messengerWriteUrl(): string
    {
        $links = $this->socialLinks();

        foreach ($links as $link) {
            $url = (string) ($link['url'] ?? '');
            if ($url !== '' && preg_match('/whatsapp|wa\.me/i', $url) === 1) {
                return $url;
            }
        }

        foreach ($links as $link) {
            $url = (string) ($link['url'] ?? '');
            if ($url !== '' && preg_match('/t\.me|telegram/i', $url) === 1) {
                return $url;
            }
        }

        $first = $links[0]['url'] ?? '';

        return is_string($first) ? $first : '';
    }
}
