<?php

namespace App\Filament\Support;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;

final class SiteLeadTableSearch
{
    public static function apply(Builder $query, string $search): Builder
    {
        $search = trim($search);

        if ($search === '') {
            return $query;
        }

        $digits = preg_replace('/\D+/', '', $search) ?? '';

        $query->where(function (Builder $inner) use ($search, $digits): void {
            self::applyFullNameSearch($inner, $search);
            self::applyPhoneSearch($inner, $search, $digits);
        });

        return $query;
    }

    private static function applyFullNameSearch(Builder $query, string $search): void
    {
        $driver = $query->getConnection()->getDriverName();

        $query->where(function (Builder $nameQuery) use ($search, $driver): void {
            if ($driver === 'pgsql') {
                $nameQuery->where('full_name', 'ilike', '%'.$search.'%');

                return;
            }

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $nameQuery->where('full_name', 'like', '%'.$search.'%');

                return;
            }

            $isFirst = true;

            foreach (self::caseVariants($search) as $variant) {
                $clause = $isFirst ? 'where' : 'orWhere';
                $nameQuery->{$clause}('full_name', 'like', '%'.$variant.'%');
                $isFirst = false;
            }
        });
    }

    private static function applyPhoneSearch(Builder $query, string $search, string $digits): void
    {
        $query->orWhere(function (Builder $phoneQuery) use ($search, $digits): void {
            $phoneQuery->where('phone', 'like', '%'.$search.'%');

            if ($digits === '') {
                return;
            }

            $expression = self::digitsOnlyPhoneExpression($phoneQuery->getConnection());
            $phoneQuery->orWhereRaw("{$expression} LIKE ?", ['%'.$digits.'%']);
        });
    }

    private static function digitsOnlyPhoneExpression(Connection $connection): string
    {
        return match ($connection->getDriverName()) {
            'mysql', 'mariadb' => "REGEXP_REPLACE(phone, '[^0-9]', '')",
            'pgsql' => "REGEXP_REPLACE(phone, '[^0-9]', '', 'g')",
            default => "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '')",
        };
    }

    /**
     * @return list<string>
     */
    private static function caseVariants(string $search): array
    {
        return array_values(array_unique([
            $search,
            mb_strtolower($search),
            mb_strtoupper($search),
            mb_convert_case($search, MB_CASE_TITLE, 'UTF-8'),
        ]));
    }
}
