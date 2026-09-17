<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_pricing_lines')) {
            return;
        }

        if (Schema::hasColumn('order_pricing_lines', 'work_entry_id')) {
            return;
        }

        if (! Schema::hasColumn('order_pricing_lines', 'order_item_id')) {
            return;
        }

        // Старые строки по order_item_id семантически невалидны.
        DB::table('order_pricing_lines')->delete();

        Schema::table('order_pricing_lines', function (Blueprint $table) {
            $table->dropUnique(['pricing_id', 'order_item_id']);
        });

        Schema::table('order_pricing_lines', function (Blueprint $table) {
            $table->renameColumn('order_item_id', 'work_entry_id');
        });

        Schema::table('order_pricing_lines', function (Blueprint $table) {
            $table->unique(['pricing_id', 'work_entry_id']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('order_pricing_lines')) {
            return;
        }

        if (! Schema::hasColumn('order_pricing_lines', 'work_entry_id')) {
            return;
        }

        if (Schema::hasColumn('order_pricing_lines', 'order_item_id')) {
            return;
        }

        DB::table('order_pricing_lines')->delete();

        Schema::table('order_pricing_lines', function (Blueprint $table) {
            $table->dropUnique(['pricing_id', 'work_entry_id']);
        });

        Schema::table('order_pricing_lines', function (Blueprint $table) {
            $table->renameColumn('work_entry_id', 'order_item_id');
        });

        Schema::table('order_pricing_lines', function (Blueprint $table) {
            $table->unique(['pricing_id', 'order_item_id']);
        });
    }
};
