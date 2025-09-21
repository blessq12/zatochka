<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Сначала удаляем индексы, связанные со складом
            if (Schema::hasIndex('stock_movements', 'stock_movements_warehouse_id_movement_date_index')) {
                $table->dropIndex(['warehouse_id', 'movement_date']);
            }
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            // Удаляем внешний ключ, если он еще существует
            if (Schema::hasColumn('stock_movements', 'warehouse_id')) {
                // Проверяем, существует ли foreign key constraint
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'stock_movements' 
                    AND COLUMN_NAME = 'warehouse_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if (!empty($foreignKeys)) {
                    $table->dropForeign(['warehouse_id']);
                }
                
                $table->dropColumn('warehouse_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Восстанавливаем колонку
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
        });
    }
};
