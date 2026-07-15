<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        if (! Schema::hasColumn('orders', 'service_type')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('service_type')->default('repair')->after('status');
                $table->string('billing_type')->default('paid')->after('service_type');
                $table->string('urgency')->default('normal')->after('billing_type');
                $table->boolean('delivery_required')->default(false)->after('urgency');
                $table->text('defects')->nullable()->after('delivery_required');
                $table->text('internal_notes')->nullable()->after('defects');
            });
        }

        if (Schema::hasTable('order_items') && ! Schema::hasColumn('order_items', 'tool_name')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->string('tool_name')->nullable()->after('client_equipment_id');
            });
        }
    }

    public function down(): void
    {
        // Irreversible additive migration for local greenfield DBs.
    }
};
