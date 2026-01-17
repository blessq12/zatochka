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
        if (!Schema::hasTable('order_work_materials')) {
            Schema::create('order_work_materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('work_id')->constrained('works')->onDelete('cascade');
                $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
                $table->foreignId('warehouse_item_id')->nullable()->constrained('warehouse_items')->onDelete('set null');
                
                // Snapshot данных материала на момент списания
                $table->string('name');
                $table->string('article')->nullable();
                $table->string('category_name')->nullable();
                $table->string('unit', 20)->default('шт');
                $table->decimal('price', 10, 2);
                $table->decimal('quantity', 10, 3);
                $table->text('notes')->nullable();
                
                $table->timestamps();
                
                $table->index(['work_id', 'order_id']);
                $table->index('warehouse_item_id');
            });
        } else {
            // Таблица уже существует, добавляем только недостающий внешний ключ
            Schema::table('order_work_materials', function (Blueprint $table) {
                // Проверяем, существует ли колонка warehouse_item_id
                if (!Schema::hasColumn('order_work_materials', 'warehouse_item_id')) {
                    $table->foreignId('warehouse_item_id')->nullable()->after('order_id');
                }
                
                // Проверяем, существует ли внешний ключ
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'order_work_materials' 
                    AND COLUMN_NAME = 'warehouse_item_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if (empty($foreignKeys) && Schema::hasColumn('order_work_materials', 'warehouse_item_id')) {
                    $table->foreign('warehouse_item_id')
                        ->references('id')
                        ->on('warehouse_items')
                        ->onDelete('set null');
                }
                
                // Добавляем индекс, если его нет
                if (Schema::hasColumn('order_work_materials', 'warehouse_item_id')) {
                    try {
                        $table->index('warehouse_item_id');
                    } catch (\Exception $e) {
                        // Индекс уже существует, игнорируем
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_work_materials');
    }
};
