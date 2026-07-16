<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'warranty_source_order_id')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('warranty_source_order_id', 32)->nullable()->after('internal_notes');
            });
        }

        if (! Schema::hasTable('order_items')) {
            return;
        }

        if (! Schema::hasColumn('order_items', 'tool_type')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->string('tool_type')->nullable()->after('tool_name');
            });
        }

        if (! Schema::hasColumn('order_items', 'quantity')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->unsignedInteger('quantity')->nullable()->after('tool_type');
            });
        }

        DB::table('order_items')
            ->whereNotNull('tool_name')
            ->whereNull('tool_type')
            ->update([
                'tool_type' => 'other',
                'quantity' => 1,
            ]);
    }

    public function down(): void
    {
        // Irreversible additive migration for local greenfield DBs.
    }
};
