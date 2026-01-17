<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->onDelete('cascade');
                $table->foreignId('branch_id')->constrained()->onDelete('cascade');
                $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('master_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('order_number')->unique();
                $table->string('service_type');
                $table->string('status')->default('new');
                $table->enum('urgency', ['normal', 'urgent'])->default('normal');
                $table->foreignId('discount_id')->nullable()->constrained('discount_rules')->onDelete('set null');
                $table->text('internal_notes')->nullable();
                $table->text('problem_description')->nullable();
                $table->boolean('is_deleted')->default(false);
                $table->timestamps();
                $table->timestamp('deleted_at')->nullable();
                $table->decimal('estimated_price', 10, 2)->nullable()->comment('Ориентировочная цена при приеме');
                $table->decimal('actual_price', 10, 2)->nullable()->comment('Фактическая стоимость после ремонта');
                $table->text('delivery_address')->nullable()->comment('Адрес доставки');
                $table->decimal('delivery_cost', 10, 2)->nullable()->comment('Стоимость доставки');
                $table->string('equipment_name')->nullable()->comment('Наименование инструмента/оборудования');
                $table->string('equipment_serial_number')->nullable()->comment('Серийный номер оборудования');
                $table->enum('order_payment_type', ['paid', 'warranty'])->default('paid')->comment('Тип оплаты: платный или гарантия');
                $table->string('equipment_type')->nullable();
                $table->string('tool_type')->nullable();
                $table->integer('total_tools_count')->nullable();
                $table->boolean('needs_delivery')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
