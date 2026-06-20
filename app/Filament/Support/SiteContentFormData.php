<?php

namespace App\Filament\Support;

final class SiteContentFormData
{
    /** @return array<string, mixed> */
    public static function contactsToForm(array $value): array
    {
        $address = $value['address'] ?? [];

        return [
            'contact_person' => $value['contact_person'] ?? '',
            'phone' => $value['phone'] ?? '',
            'phone_tel' => $value['phone_tel'] ?? '',
            'email' => $value['email'] ?? '',
            'address_main' => $address['main'] ?? '',
            'address_details' => array_values($address['details'] ?? []),
            'social_email' => $value['social']['email'] ?? '',
            'social_links' => $value['social']['links'] ?? [],
        ];
    }

    /** @return array<string, mixed> */
    public static function contactsFromForm(array $data): array
    {
        $addressDetails = [];

        foreach ($data['address_details'] ?? [] as $row) {
            $text = is_array($row) ? ($row['text'] ?? null) : $row;

            if (is_string($text) && $text !== '') {
                $addressDetails[] = $text;
            }
        }

        return [
            'contact_person' => $data['contact_person'] ?? '',
            'phone' => $data['phone'] ?? '',
            'phone_tel' => $data['phone_tel'] ?? '',
            'email' => $data['email'] ?? '',
            'address' => [
                'main' => $data['address_main'] ?? '',
                'details' => $addressDetails,
            ],
            'social' => [
                'email' => $data['social_email'] ?? '',
                'links' => array_values($data['social_links'] ?? []),
            ],
        ];
    }

    /** @return array{days: list<array<string, mixed>>} */
    public static function scheduleToForm(array $value): array
    {
        return [
            'days' => collect($value['days'] ?? [])
                ->map(fn (array $day): array => [
                    'name' => $day['name'] ?? '',
                    'is_day_off' => (bool) ($day['is_day_off'] ?? false),
                    'day_off_text' => $day['day_off_text'] ?? '',
                    'workshop' => $day['workshop'] ?? '',
                    'delivery' => $day['delivery'] ?? '',
                ])
                ->values()
                ->all(),
        ];
    }

    /** @return array{days: list<array<string, mixed>>} */
    public static function scheduleFromForm(array $data): array
    {
        $days = [];

        foreach ($data['days'] ?? [] as $index => $day) {
            $isDayOff = (bool) ($day['is_day_off'] ?? false);

            $entry = [
                'id' => $index + 1,
                'name' => $day['name'] ?? '',
                'is_day_off' => $isDayOff,
            ];

            if ($isDayOff) {
                $entry['day_off_text'] = $day['day_off_text'] ?? '';
            } else {
                $entry['workshop'] = $day['workshop'] ?? '';
                $entry['delivery'] = $day['delivery'] ?? '';
            }

            $days[] = $entry;
        }

        return ['days' => $days];
    }

    /** @return array<string, string> */
    public static function companyToForm(array $value): array
    {
        return [
            'name' => $value['name'] ?? '',
            'owner_name' => $value['owner_name'] ?? '',
            'inn' => $value['inn'] ?? '',
            'ogrn' => $value['ogrn'] ?? '',
            'legal_address' => $value['legal_address'] ?? '',
            'actual_address' => $value['actual_address'] ?? '',
        ];
    }

    /** @return array<string, string> */
    public static function companyFromForm(array $data): array
    {
        return [
            'name' => $data['name'] ?? '',
            'owner_name' => $data['owner_name'] ?? '',
            'inn' => $data['inn'] ?? '',
            'ogrn' => $data['ogrn'] ?? '',
            'legal_address' => $data['legal_address'] ?? '',
            'actual_address' => $data['actual_address'] ?? '',
        ];
    }

    /** @return array{free_conditions: list<string>, advantages: list<array<string, string>>} */
    public static function deliveryToForm(array $value): array
    {
        return [
            'free_conditions' => array_values($value['free_conditions'] ?? []),
            'advantages' => $value['advantages'] ?? [],
        ];
    }

    /** @return array{free_conditions: list<string>, advantages: list<array<string, string>>} */
    public static function deliveryFromForm(array $data): array
    {
        $freeConditions = [];

        foreach ($data['free_conditions'] ?? [] as $row) {
            $text = is_array($row) ? ($row['text'] ?? null) : $row;

            if (is_string($text) && $text !== '') {
                $freeConditions[] = $text;
            }
        }

        return [
            'free_conditions' => $freeConditions,
            'advantages' => $data['advantages'] ?? [],
        ];
    }

    /** @return array{items: list<array{question: string, answer_lines: list<string>}>} */
    public static function faqToForm(array $value): array
    {
        return [
            'items' => collect($value['items'] ?? [])
                ->map(fn (array $item): array => [
                    'question' => $item['question'] ?? '',
                    'answer_lines' => array_values($item['answer_lines'] ?? []),
                ])
                ->values()
                ->all(),
        ];
    }

    /** @return array{items: list<array{id: int, question: string, answer_lines: list<string>}>} */
    public static function faqFromForm(array $data): array
    {
        $items = [];

        foreach ($data['items'] ?? [] as $index => $item) {
            $answerLines = [];

            foreach ($item['answer_lines'] ?? [] as $row) {
                $line = is_array($row) ? ($row['line'] ?? null) : $row;

                if (is_string($line) && $line !== '') {
                    $answerLines[] = $line;
                }
            }

            $items[] = [
                'id' => $index + 1,
                'question' => $item['question'] ?? '',
                'answer_lines' => $answerLines,
            ];
        }

        return ['items' => $items];
    }

    /**
     * @param  array<string, array<string, mixed>>  $settings
     * @return array{contacts: array<string, mixed>, schedule: array<string, mixed>, company: array<string, string>, delivery_info: array<string, mixed>, faq: array<string, mixed>}
     */
    public static function allToForm(array $settings): array
    {
        return [
            'contacts' => self::contactsToForm($settings['contacts'] ?? []),
            'schedule' => self::scheduleToForm($settings['schedule'] ?? []),
            'company' => self::companyToForm($settings['company'] ?? []),
            'delivery_info' => self::deliveryToForm($settings['delivery_info'] ?? []),
            'faq' => self::faqToForm($settings['faq'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{contacts: array<string, mixed>, schedule: array<string, mixed>, company: array<string, string>, delivery_info: array<string, mixed>, faq: array<string, mixed>}
     */
    public static function allFromForm(array $data): array
    {
        return [
            'contacts' => self::contactsFromForm($data['contacts'] ?? []),
            'schedule' => self::scheduleFromForm($data['schedule'] ?? []),
            'company' => self::companyFromForm($data['company'] ?? []),
            'delivery_info' => self::deliveryFromForm($data['delivery_info'] ?? []),
            'faq' => self::faqFromForm($data['faq'] ?? []),
        ];
    }
}
