<?php

namespace App\Application\SiteContent\Presenter;

final class SiteContentPresenter
{
    /**
     * @param  array<string, mixed>  $raw
     * @return array{
     *     company: array<string, mixed>,
     *     contacts: array<string, mixed>,
     *     schedule: array<string, mixed>,
     *     faq: array<string, mixed>,
     *     delivery_info: array<string, mixed>,
     *     prices: list<array<string, mixed>>,
     *     sharpening_prices: list<array<string, mixed>>,
     *     repair_prices: list<array<string, mixed>>
     * }
     */
    public function present(array $raw): array
    {
        $contacts = (array) ($raw['contacts'] ?? []);
        $phone = (string) ($contacts['phone'] ?? '');
        $contacts['phone_tel'] = $this->phoneTel($phone);
        $contacts['messenger_write_url'] = $this->messengerWriteUrl(
            array_values((array) (($contacts['social']['links'] ?? []) ?: [])),
        );

        $prices = array_values(array_map(
            fn (array $item): array => $this->presentPrice($item),
            array_values((array) ($raw['prices'] ?? [])),
        ));

        return [
            'company' => (array) ($raw['company'] ?? []),
            'contacts' => $contacts,
            'schedule' => (array) ($raw['schedule'] ?? ['days' => []]),
            'faq' => (array) ($raw['faq'] ?? ['items' => []]),
            'delivery_info' => (array) ($raw['delivery_info'] ?? [
                'free_conditions' => [],
                'advantages' => [],
            ]),
            'prices' => $prices,
            'sharpening_prices' => array_values(array_filter(
                $prices,
                static fn (array $item): bool => ($item['category'] ?? '') === 'sharpening',
            )),
            'repair_prices' => array_values(array_filter(
                $prices,
                static fn (array $item): bool => ($item['category'] ?? '') === 'repair',
            )),
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function presentPrice(array $item): array
    {
        $price = (string) ($item['price'] ?? '');
        $prefix = $item['prefix'] ?? null;
        $display = $price === '' ? '' : $price.'₽';

        $item['display_price'] = match ($prefix) {
            'from' => $display !== '' ? 'от '.$display : '',
            'to' => $display !== '' ? 'до '.$display : '',
            default => $display,
        };

        return $item;
    }

    private function phoneTel(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

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

    /**
     * @param  list<array<string, mixed>>  $links
     */
    private function messengerWriteUrl(array $links): string
    {
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
