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
            // Добавляем новый уникальный индекс только по name
            $table->unique('name', 'stock_categories_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_categories', function (Blueprint $table) {
            // Удаляем уникальный индекс
            $table->dropUnique('stock_categories_name_unique');
        });
    }
};
