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
        Schema::table('orders', function (Blueprint $table) {
            // Обрабатываем старое поле type (заменено на service_type)
            // Если поле type существует, делаем его nullable через прямой SQL
            if (Schema::hasColumn('orders', 'type')) {
                try {
                    // Пытаемся изменить поле type на nullable через прямой SQL
                    DB::statement("ALTER TABLE `orders` MODIFY COLUMN `type` VARCHAR(255) NULL DEFAULT NULL");
                } catch (\Exception $e) {
                    // Если не получилось, пытаемся удалить
                    try {
                        $table->dropColumn('type');
                    } catch (\Exception $e2) {
                        // Если и удаление не сработало, игнорируем
                    }
                }
            }

            // Добавляем service_type, если его нет (самое важное поле!)
            if (!Schema::hasColumn('orders', 'service_type')) {
                $table->string('service_type')->after('order_number');
            }

            // Добавляем status, если его нет
            if (!Schema::hasColumn('orders', 'status')) {
                $table->string('status')->default('new')->after('service_type');
            }

            // Добавляем internal_notes, если его нет
            if (!Schema::hasColumn('orders', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('discount_id');
            }

            // Добавляем problem_description, если его нет
            if (!Schema::hasColumn('orders', 'problem_description')) {
                $table->text('problem_description')->nullable()->after('internal_notes');
            }

            // Добавляем is_deleted, если его нет
            if (!Schema::hasColumn('orders', 'is_deleted')) {
                $table->boolean('is_deleted')->default(false)->after('problem_description');
            }

            // Добавляем deleted_at, если его нет (для SoftDeletes)
            if (!Schema::hasColumn('orders', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
            }

            // Добавляем estimated_price, если его нет
            if (!Schema::hasColumn('orders', 'estimated_price')) {
                $table->decimal('estimated_price', 10, 2)->nullable()->after('internal_notes');
            }

            // Добавляем actual_price, если его нет
            if (!Schema::hasColumn('orders', 'actual_price')) {
                $table->decimal('actual_price', 10, 2)->nullable()->after('estimated_price');
            }

            // Добавляем delivery_address, если его нет
            if (!Schema::hasColumn('orders', 'delivery_address')) {
                $table->text('delivery_address')->nullable()->after('actual_price');
            }

            // Добавляем needs_delivery, если его нет
            if (!Schema::hasColumn('orders', 'needs_delivery')) {
                $table->boolean('needs_delivery')->default(false)->after('delivery_address');
            }

            // Добавляем delivery_cost, если его нет
            if (!Schema::hasColumn('orders', 'delivery_cost')) {
                $table->decimal('delivery_cost', 10, 2)->nullable()->after('needs_delivery');
            }

            // Добавляем equipment_name, если его нет (делаем nullable)
            if (!Schema::hasColumn('orders', 'equipment_name')) {
                $table->string('equipment_name')->nullable()->after('delivery_cost');
            } else {
                // Если есть, но NOT NULL - делаем nullable
                try {
                    $table->string('equipment_name')->nullable()->change();
                } catch (\Exception $e) {
                    // Игнорируем ошибку, если не удалось изменить
                }
            }

            // Добавляем equipment_type, если его нет
            if (!Schema::hasColumn('orders', 'equipment_type')) {
                $table->string('equipment_type')->nullable()->after('equipment_name');
            }

            // Добавляем tool_type, если его нет
            if (!Schema::hasColumn('orders', 'tool_type')) {
                $table->string('tool_type')->nullable()->after('equipment_type');
            }

            // Добавляем total_tools_count, если его нет
            if (!Schema::hasColumn('orders', 'total_tools_count')) {
                $table->integer('total_tools_count')->nullable()->after('tool_type');
            }

            // Добавляем equipment_serial_number, если его нет
            if (!Schema::hasColumn('orders', 'equipment_serial_number')) {
                $table->string('equipment_serial_number')->nullable()->after('equipment_name');
            }

            // Добавляем order_payment_type, если его нет
            if (!Schema::hasColumn('orders', 'order_payment_type')) {
                $table->enum('order_payment_type', ['paid', 'warranty'])->default('paid')->after('equipment_serial_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Не делаем rollback, так как это миграция синхронизации
    }
};
