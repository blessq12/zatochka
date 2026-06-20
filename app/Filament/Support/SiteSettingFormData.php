<?php

namespace App\Filament\Support;

final class SiteSettingFormData
{
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
}
