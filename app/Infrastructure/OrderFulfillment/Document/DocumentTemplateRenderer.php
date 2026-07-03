<?php

namespace App\Infrastructure\OrderFulfillment\Document;

use App\Application\OrderFulfillment\Port\DocumentTemplateRendererInterface;
use App\Application\OrderFulfillment\ReadModel\OrderDocumentData;
use App\Application\OrderFulfillment\Support\DocumentTemplateVariableResolver;

final class DocumentTemplateRenderer implements DocumentTemplateRendererInterface
{
    public function __construct(
        private DocumentTemplateVariableResolver $variableResolver,
    ) {}

    public function render(string $body, OrderDocumentData $data, string $documentTitle): string
    {
        $variables = $this->variableResolver->resolve($data, $documentTitle);

        $html = $this->processEachBlocks($body, $variables);
        $html = $this->processConditionalBlocks($html, $variables, 'if');
        $html = $this->processConditionalBlocks($html, $variables, 'unless');

        return $this->replaceScalars($html, $variables);
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    private function processEachBlocks(string $body, array $variables): string
    {
        return (string) preg_replace_callback(
            '/\{\{#each\s+([a-z0-9_.]+)\}\}(.*?)\{\{\/each\}\}/si',
            function (array $matches) use ($variables): string {
                $key = $matches[1];
                $template = $matches[2];
                $items = $variables[$key] ?? [];

                if (! is_array($items)) {
                    return '';
                }

                $result = '';
                foreach ($items as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $chunk = $template;
                    foreach ($item as $field => $value) {
                        $chunk = str_replace('{{'.$field.'}}', $this->escapeValue((string) $value), $chunk);
                    }
                    $result .= $chunk;
                }

                return $result;
            },
            $body,
        );
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    private function processConditionalBlocks(string $body, array $variables, string $mode): string
    {
        $pattern = $mode === 'if'
            ? '/\{\{#if\s+([a-z0-9_.]+)\}\}(.*?)\{\{\/if\}\}/si'
            : '/\{\{#unless\s+([a-z0-9_.]+)\}\}(.*?)\{\{\/unless\}\}/si';

        return (string) preg_replace_callback(
            $pattern,
            function (array $matches) use ($variables, $mode): string {
                $key = $matches[1];
                $content = $matches[2];
                $value = $variables[$key] ?? null;
                $isTruthy = $this->isTruthy($value);
                $show = $mode === 'if' ? $isTruthy : ! $isTruthy;

                return $show ? $content : '';
            },
            $body,
        );
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    private function replaceScalars(string $body, array $variables): string
    {
        return (string) preg_replace_callback(
            '/\{\{([a-z0-9_.]+)\}\}/i',
            function (array $matches) use ($variables): string {
                $key = $matches[1];
                $value = $variables[$key] ?? '';

                if (is_array($value)) {
                    return '';
                }

                if ($this->variableResolver->isRawHtmlKey($key)) {
                    return (string) $value;
                }

                return $this->escapeValue((string) $value);
            },
            $body,
        );
    }

    private function isTruthy(mixed $value): bool
    {
        if (is_array($value)) {
            return $value !== [];
        }

        return $value !== null && $value !== '';
    }

    private function escapeValue(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
