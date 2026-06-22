<?php

namespace App\Filament\Support;

use App\Domain\Equipment\ValueObject\ComponentSerialNumbers;

final class EquipmentFormData
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    public static function serialNumbersFromForm(array $data): array
    {
        return self::serialNumbersFromFormField($data, 'serial_numbers');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    public static function serialNumbersFromOrderForm(array $data): array
    {
        return self::serialNumbersFromFormField($data, 'equipment_serial_numbers');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    public static function serialNumbersFromFormField(array $data, string $field): array
    {
        return ComponentSerialNumbers::fromFormRows($data[$field] ?? [])->toStorage();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function prepareForForm(array $data): array
    {
        $data['serial_numbers'] = ComponentSerialNumbers::fromStorage($data['serial_numbers'] ?? [])
            ->toFormRows();

        return $data;
    }

    public static function formatForDisplay(mixed $serialNumbers): string
    {
        return ComponentSerialNumbers::fromStorage($serialNumbers)->formatForDisplay();
    }

    public static function formatForListDisplay(mixed $serialNumbers): string
    {
        return ComponentSerialNumbers::fromStorage($serialNumbers)->formatForListDisplay();
    }

    /**
     * @return list<array{component: string, serial: string}>
     */
    public static function displayRows(mixed $serialNumbers): array
    {
        return ComponentSerialNumbers::fromStorage($serialNumbers)->toFormRows();
    }
}
