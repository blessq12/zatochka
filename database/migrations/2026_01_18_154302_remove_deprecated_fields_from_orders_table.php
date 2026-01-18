<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Удаляем поля, которые не относятся к бизнес-модели заказа
     * Эти данные теперь хранятся в отдельных таблицах:
     * - equipment_id -> связь с Equipment
     * - tools -> связь hasMany с SharpeningTool
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Удаляем deprecated поля оборудования (теперь используется equipment_id)
            if (Schema::hasColumn('orders', 'equipment_name')) {
                $table->dropColumn('equipment_name');
            }
            if (Schema::hasColumn('orders', 'equipment_type')) {
                $table->dropColumn('equipment_type');
            }
            if (Schema::hasColumn('orders', 'equipment_serial_number')) {
                $table->dropColumn('equipment_serial_number');
            }

            // Удаляем deprecated поля инструментов (теперь используется связь hasMany с Tool)
            if (Schema::hasColumn('orders', 'tool_type')) {
                $table->dropColumn('tool_type');
            }
            if (Schema::hasColumn('orders', 'total_tools_count')) {
                $table->dropColumn('total_tools_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Восстанавливаем поля для отката (но они deprecated)
            $table->string('equipment_name')->nullable();
            $table->string('equipment_type')->nullable();
            $table->string('equipment_serial_number')->nullable();
            $table->string('tool_type')->nullable();
            $table->integer('total_tools_count')->nullable();
        });
    }
};
