<?php

namespace App\Infrastructure\Shared\Persistence;

use App\Shared\ValueObject\EntityId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class SequentialEntityIdGenerator
{
    /**
     * Sequence name → table used to keep next_value ahead of existing rows.
     *
     * @var array<string, string>
     */
    private const TABLE_BY_SEQUENCE = [
        'order_item' => 'order_items',
        'performed_work' => 'performed_works',
        'production_task' => 'production_tasks',
        'work_execution' => 'work_executions',
        'diagnosis' => 'diagnoses',
        'work_price' => 'work_prices',
        'client' => 'clients',
        'equipment' => 'client_equipment',
        'equipment_component' => 'equipment_components',
        'reception' => 'reception_data',
        'estimate' => 'estimates',
        'item_price' => 'item_prices',
        'discount' => 'discounts',
        'stock_item' => 'stock_items',
        'material' => 'materials',
        'warehouse_movement' => 'warehouse_movements',
        'payment' => 'payments',
        'refund' => 'refunds',
        'cash_operation' => 'cash_operations',
        'delivery_request' => 'delivery_requests',
    ];

    public function next(string $name): EntityId
    {
        return DB::transaction(function () use ($name): EntityId {
            $row = DB::table('entity_id_sequences')
                ->where('name', $name)
                ->lockForUpdate()
                ->first();

            $value = $row === null ? 1 : (int) $row->next_value;
            $floor = $this->existingMaxId($name) + 1;

            if ($value < $floor) {
                $value = $floor;
            }

            if ($row === null) {
                DB::table('entity_id_sequences')->insert([
                    'name' => $name,
                    'next_value' => $value + 1,
                ]);
            } else {
                DB::table('entity_id_sequences')
                    ->where('name', $name)
                    ->update(['next_value' => $value + 1]);
            }

            return new EntityId($value);
        });
    }

    private function existingMaxId(string $sequenceName): int
    {
        if ($sequenceName === 'order_number') {
            return $this->existingMaxOrderNumberSequence();
        }

        $table = self::TABLE_BY_SEQUENCE[$sequenceName] ?? null;

        if ($table === null || ! Schema::hasTable($table)) {
            return 0;
        }

        return (int) (DB::table($table)->max('id') ?? 0);
    }

    private function existingMaxOrderNumberSequence(): int
    {
        if (! Schema::hasTable('orders')) {
            return 0;
        }

        $yearPrefix = 'ORD-'.now()->format('y').'-';
        $max = 0;

        foreach (DB::table('orders')->where('number', 'like', $yearPrefix.'%')->pluck('number') as $number) {
            $parts = explode('-', (string) $number);
            $sequence = (int) ($parts[2] ?? 0);

            if ($sequence > $max) {
                $max = $sequence;
            }
        }

        return $max;
    }
}
