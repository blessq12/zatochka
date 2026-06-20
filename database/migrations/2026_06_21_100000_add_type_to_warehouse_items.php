<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('warehouse_items', 'category_name')) {
            return;
        }

        Schema::table('warehouse_items', function (Blueprint $table) {
            $table->string('type')->default('consumable')->after('sku');
        });

        DB::table('warehouse_items')
            ->where('category_name', 'Запчасти')
            ->update(['type' => 'spare_part']);

        DB::table('warehouse_items')
            ->where('category_name', 'Расходники')
            ->update(['type' => 'consumable']);

        Schema::table('warehouse_items', function (Blueprint $table) {
            $table->dropColumn('category_name');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('warehouse_items', 'type')) {
            return;
        }

        Schema::table('warehouse_items', function (Blueprint $table) {
            $table->string('category_name')->nullable()->after('sku');
        });

        DB::table('warehouse_items')
            ->where('type', 'spare_part')
            ->update(['category_name' => 'Запчасти']);

        DB::table('warehouse_items')
            ->where('type', 'consumable')
            ->update(['category_name' => 'Расходники']);

        Schema::table('warehouse_items', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
