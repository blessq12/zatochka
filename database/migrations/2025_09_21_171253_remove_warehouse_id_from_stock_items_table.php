<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            // Сначала удаляем внешний ключ
            if (Schema::hasColumn('stock_items', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
            }
        });

        Schema::table('stock_items', function (Blueprint $table) {
            // Затем удаляем индексы, связанные со складом
            if (Schema::hasIndex('stock_items', 'stock_items_warehouse_id_category_id_index')) {
                $table->dropIndex(['warehouse_id', 'category_id']);
            }
            if (Schema::hasIndex('stock_items', 'stock_items_warehouse_id_is_active_index')) {
                $table->dropIndex(['warehouse_id', 'is_active']);
            }

            // Удаляем колонку
            if (Schema::hasColumn('stock_items', 'warehouse_id')) {
                $table->dropColumn('warehouse_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->index(['warehouse_id', 'category_id']);
            $table->index(['warehouse_id', 'is_active']);
        });
    }
};
