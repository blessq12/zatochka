<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneFormat implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return; // Пропускаем пустые значения (nullable)
        }

        // Убираем все пробелы и приводим к строке
        $phone = trim((string) $value);

        // Проверяем различные форматы телефонов
        $patterns = [
            // +7 (999) 999-99-99
            '/^\+7\s*\(\d{3}\)\s*\d{3}-\d{2}-\d{2}$/',
            // +7 (999) 999-99-99 (без пробелов)
            '/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/',
            // +7 999 999-99-99
            '/^\+7\s*\d{3}\s*\d{3}-\d{2}-\d{2}$/',
            // +7 999 999 99 99
            '/^\+7\s*\d{3}\s*\d{3}\s*\d{2}\s*\d{2}$/',
            // 8 (999) 999-99-99
            '/^8\s*\(\d{3}\)\s*\d{3}-\d{2}-\d{2}$/',
            // 8 999 999-99-99
            '/^8\s*\d{3}\s*\d{3}-\d{2}-\d{2}$/',
            // 999 999-99-99 (без кода страны)
            '/^\d{3}\s*\d{3}-\d{2}-\d{2}$/',
        ];

        $isValid = false;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                $isValid = true;
                break;
            }
        }

        if (!$isValid) {
            $fail('Поле :attribute должно содержать корректный номер телефона. Поддерживаемые форматы: +7 (999) 999-99-99, +7 999 999-99-99, 8 (999) 999-99-99');
        }
    }
}
