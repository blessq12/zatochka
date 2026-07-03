<?php

namespace App\Domain\Equipment\ValueObject;

final readonly class ComponentSerialNumbers
{
    /**
     * @param  array<string, string>  $items
     */
    private function __construct(private array $items) {}

    public static function empty(): self
    {
        return new self([]);
    }

    public static function fromStorage(mixed $raw): self
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);

            return is_array($decoded)
                ? self::fromStorage($decoded)
                : self::empty();
        }

        if (! is_array($raw) || $raw === []) {
            return self::empty();
        }

        if (array_is_list($raw)) {
            $items = [];

            foreach ($raw as $index => $entry) {
                if (is_array($entry)) {
                    $component = trim((string) ($entry['component'] ?? ''));
                    $serial = trim((string) ($entry['serial'] ?? ($entry['value'] ?? '')));

                    if ($component !== '' && $serial !== '') {
                        $items[$component] = $serial;
                    }

                    continue;
                }

                if (! is_string($entry) || $entry === '') {
                    continue;
                }

                $items['Компонент '.($index + 1)] = $entry;
            }

            return new self($items);
        }

        $items = [];

        foreach ($raw as $component => $serial) {
            if (is_string($component) && is_string($serial) && $serial !== '') {
                $items[$component] = $serial;
            }
        }

        return new self($items);
    }

    /**
     * @param  list<array{component?: string, serial?: string}>  $rows
     */
    public static function fromFormRows(array $rows): self
    {
        $items = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $component = trim((string) ($row['component'] ?? ''));
            $serial = trim((string) ($row['serial'] ?? ''));

            if ($component !== '' && $serial !== '') {
                $items[$component] = $serial;
            }
        }

        return new self($items);
    }

    /** @return array<string, string> */
    public function toStorage(): array
    {
        return $this->items;
    }

    /**
     * @return list<array{component: string, serial: string}>
     */
    public function toFormRows(): array
    {
        $rows = [];

        foreach ($this->items as $component => $serial) {
            $rows[] = ['component' => $component, 'serial' => $serial];
        }

        return $rows;
    }

    public function formatForDisplay(): string
    {
        if ($this->items === []) {
            return '—';
        }

        $lines = [];

        foreach ($this->items as $component => $serial) {
            $lines[] = $component.': '.$serial;
        }

        return implode("\n", $lines);
    }

    public function formatForListDisplay(): string
    {
        if ($this->items === []) {
            return '—';
        }

        $parts = [];

        foreach ($this->items as $component => $serial) {
            $parts[] = $component.': '.$serial;
        }

        return implode(', ', $parts);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }
}
