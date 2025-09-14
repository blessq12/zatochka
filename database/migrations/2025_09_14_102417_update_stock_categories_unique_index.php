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
        Schema::table('stock_categories', function (Blueprint $table) {
            // Добавляем новый составной уникальный индекс
            $table->unique(['warehouse_id', 'name'], 'stock_categories_warehouse_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_categories', function (Blueprint $table) {
            // Возвращаем старый индекс
            $table->dropUnique('stock_categories_warehouse_name_unique');
            $table->unique('name');
        });
    }
};
