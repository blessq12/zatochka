<?php

namespace App\Infrastructure\Shared\Persistence;

use App\Shared\ValueObject\EntityId;
use Illuminate\Support\Facades\DB;

final class SequentialEntityIdGenerator
{
    public function next(string $name): EntityId
    {
        return DB::transaction(function () use ($name): EntityId {
            $row = DB::table('entity_id_sequences')
                ->where('name', $name)
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                DB::table('entity_id_sequences')->insert([
                    'name' => $name,
                    'next_value' => 2,
                ]);

                return new EntityId(1);
            }

            $value = (int) $row->next_value;

            DB::table('entity_id_sequences')
                ->where('name', $name)
                ->update(['next_value' => $value + 1]);

            return new EntityId($value);
        });
    }
}
