<?php

namespace App\Filament\Support;

final class ClientFormData
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function prepareForForm(array $data): array
    {
        if (isset($data['birth_date']) && $data['birth_date'] !== null) {
            $data['birth_date'] = (string) $data['birth_date'];
        }

        return $data;
    }
}
