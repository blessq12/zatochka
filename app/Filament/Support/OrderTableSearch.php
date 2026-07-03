<?php

namespace App\Filament\Support;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;

final class OrderTableSearch
{
    public static function apply(Builder $query, string $search): Builder
    {
        $search = trim($search);

        if ($search === '') {
            return $query;
        }

        $digits = preg_replace('/\D+/', '', $search) ?? '';

        $query->where(function (Builder $inner) use ($search, $digits): void {
            $inner->where('order_number', 'like', '%'.$search.'%');

            $inner->orWhere(function (Builder $nameQuery) use ($search): void {
                self::applyClientFullNameSearch($nameQuery, $search);
            });

            $inner->orWhere(function (Builder $phoneQuery) use ($search, $digits): void {
                self::applyClientPhoneSearch($phoneQuery, $search, $digits);
            });
        });

        return $query;
    }

    private static function applyClientFullNameSearch(Builder $query, string $search): void
    {
        $driver = $query->getConnection()->getDriverName();

        $query->where(function (Builder $nameQuery) use ($search, $driver): void {
            if ($driver === 'pgsql') {
                $nameQuery->whereRaw("client_snapshot->>'full_name' ILIKE ?", ['%'.$search.'%']);

                return;
            }

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $nameQuery->where('client_snapshot->full_name', 'like', '%'.$search.'%');

                return;
            }

            $isFirst = true;

            foreach (self::caseVariants($search) as $variant) {
                $clause = $isFirst ? 'where' : 'orWhere';
                $nameQuery->{$clause}('client_snapshot->full_name', 'like', '%'.$variant.'%');
                $isFirst = false;
            }
        });
    }

    private static function applyClientPhoneSearch(Builder $query, string $search, string $digits): void
    {
        $query->where('client_snapshot->phone', 'like', '%'.$search.'%');

        if ($digits === '') {
            return;
        }

        $expression = self::clientPhoneDigitsExpression($query->getConnection());
        $query->orWhereRaw("{$expression} LIKE ?", ['%'.$digits.'%']);
    }

    private static function clientPhoneDigitsExpression(Connection $connection): string
    {
        return match ($connection->getDriverName()) {
            'mysql', 'mariadb' => "REGEXP_REPLACE(JSON_UNQUOTE(JSON_EXTRACT(client_snapshot, '$.phone')), '[^0-9]', '')",
            'pgsql' => "REGEXP_REPLACE(client_snapshot->>'phone', '[^0-9]', '', 'g')",
            default => "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(json_extract(client_snapshot, '$.phone'), ' ', ''), '-', ''), '(', ''), ')', ''), '+', '')",
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
