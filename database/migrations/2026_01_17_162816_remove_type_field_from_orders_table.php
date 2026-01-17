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
        // Проверяем, существует ли поле type
        if (Schema::hasColumn('orders', 'type')) {
            // Используем прямой SQL для удаления поля, так как оно может быть enum
            try {
                DB::statement("ALTER TABLE `orders` DROP COLUMN `type`");
            } catch (\Exception $e) {
                // Если не получилось удалить напрямую, пытаемся через Schema
                try {
                    Schema::table('orders', function (Blueprint $table) {
                        $table->dropColumn('type');
                    });
                } catch (\Exception $e2) {
                    // Если и это не сработало, пытаемся сделать nullable
                    try {
                        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `type` VARCHAR(255) NULL DEFAULT NULL");
                    } catch (\Exception $e3) {
                        // Игнорируем ошибку, если ничего не получилось
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Не восстанавливаем поле type, так как оно заменено на service_type
    }
};
