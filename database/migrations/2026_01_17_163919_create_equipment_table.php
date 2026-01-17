<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('equipment')) {
            Schema::create('equipment', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->onDelete('cascade');
                $table->foreignId('equipment_type_id')->nullable()->constrained('equipment_types')->onDelete('set null');
                $table->string('serial_number')->unique()->comment('Серийный номер (уникальный)');
                $table->string('name')->comment('Наименование оборудования');
                $table->string('brand')->nullable()->comment('Бренд');
                $table->string('model')->nullable()->comment('Модель');
                $table->date('purchase_date')->nullable()->comment('Дата покупки');
                $table->date('warranty_expiry')->nullable()->comment('Дата окончания гарантии');
                $table->boolean('is_active')->default(true)->comment('Активно');
                $table->boolean('is_deleted')->default(false)->comment('Удалено');
                $table->timestamps();

                $table->index('serial_number');
                $table->index('client_id');
                $table->index(['client_id', 'is_deleted']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
