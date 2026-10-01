<?php

namespace App\Infrastructure\Order\Service;

use App\Domain\Order\Service\OrderNumberGenerator;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class EloquentOrderNumberGenerator implements OrderNumberGenerator
{
    public function next(?DateTimeImmutable $at = null): string
    {
        $at ??= new DateTimeImmutable('now');
        $year = (int) $at->format('Y');
        $yy = $at->format('y');

        return DB::transaction(function () use ($year, $yy): string {
            $row = DB::table('order_number_sequences')
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                DB::table('order_number_sequences')->insert([
                    'year' => $year,
                    'last_num' => 1,
                ]);

                return sprintf('ORD-%s-%d', $yy, 1);
            }

            $next = ((int) $row->last_num) + 1;
            DB::table('order_number_sequences')
                ->where('year', $year)
                ->update(['last_num' => $next]);

            return sprintf('ORD-%s-%d', $yy, $next);
        });
    }
}
