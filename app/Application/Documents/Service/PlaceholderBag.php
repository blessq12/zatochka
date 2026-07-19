<?php

namespace App\Application\Documents\Service;

final class PlaceholderBag
{
    /** @var list<string> */
    private const RAW_HTML_KEYS = [
        'logo',
        'company.header',
        'items.section',
        'price.section',
        'works.table',
        'materials.table',
    ];

    /**
     * @param array<string, string> $placeholders
     */
    public function fill(string $templateHtml, array $placeholders): string
    {
        $html = preg_replace_callback(
            '/\{\{#if\s+([a-zA-Z0-9_.]+)\}\}(.*?)\{\{\/if\}\}/s',
            static function (array $matches) use ($placeholders): string {
                $value = $placeholders[$matches[1]] ?? '';

                return $value !== '' ? $matches[2] : '';
            },
            $templateHtml,
        ) ?? $templateHtml;

        foreach ($placeholders as $key => $value) {
            $replacement = in_array($key, self::RAW_HTML_KEYS, true)
                ? $value
                : htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html = str_replace('{{'.$key.'}}', $replacement, $html);
        }

        return $html;
    }
}
