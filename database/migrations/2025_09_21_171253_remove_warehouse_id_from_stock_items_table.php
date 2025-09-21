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
            // Удаляем внешний ключ
            $table->dropForeign(['warehouse_id']);

            // Удаляем колонку
            $table->dropColumn('warehouse_id');

            // Удаляем индексы, связанные со складом
            $table->dropIndex(['warehouse_id', 'category_id']);
            $table->dropIndex(['warehouse_id', 'is_active']);
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
