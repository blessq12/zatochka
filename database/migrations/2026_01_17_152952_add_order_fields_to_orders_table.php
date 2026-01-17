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
        Schema::table('orders', function (Blueprint $table) {
            // Проверяем и добавляем equipment_name, если его нет
            if (!Schema::hasColumn('orders', 'equipment_name')) {
                $table->string('equipment_name')->nullable();
            }
            
            // Поля для ремонта
            if (!Schema::hasColumn('orders', 'equipment_type')) {
                $table->string('equipment_type')->nullable();
            }
            
            // Поля для заточки
            if (!Schema::hasColumn('orders', 'tool_type')) {
                $table->string('tool_type')->nullable();
            }
            if (!Schema::hasColumn('orders', 'total_tools_count')) {
                $table->integer('total_tools_count')->nullable();
            }
            
            // Проверяем и добавляем delivery_address, если его нет
            if (!Schema::hasColumn('orders', 'delivery_address')) {
                $table->text('delivery_address')->nullable();
            }
            
            // Поле для доставки
            if (!Schema::hasColumn('orders', 'needs_delivery')) {
                $table->boolean('needs_delivery')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'tool_type')) {
                $table->dropColumn('tool_type');
            }
            if (Schema::hasColumn('orders', 'total_tools_count')) {
                $table->dropColumn('total_tools_count');
            }
            if (Schema::hasColumn('orders', 'equipment_type')) {
                $table->dropColumn('equipment_type');
            }
            if (Schema::hasColumn('orders', 'needs_delivery')) {
                $table->dropColumn('needs_delivery');
            }
        });
    }
};
