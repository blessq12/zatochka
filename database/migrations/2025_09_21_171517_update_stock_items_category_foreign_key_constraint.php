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
            // Удаляем старый внешний ключ
            $table->dropForeign(['category_id']);

            // Добавляем новый внешний ключ с CASCADE
            $table->foreign('category_id')
                ->references('id')
                ->on('stock_categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            // Удаляем внешний ключ с CASCADE
            $table->dropForeign(['category_id']);

            // Восстанавливаем старый внешний ключ с RESTRICT
            $table->foreign('category_id')
                ->references('id')
                ->on('stock_categories')
                ->onDelete('restrict');
        });
    }
};
