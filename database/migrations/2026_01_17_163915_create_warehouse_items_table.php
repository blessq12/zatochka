<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('warehouse_items')) {
            Schema::create('warehouse_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_category_id')->constrained()->onDelete('restrict');
                $table->string('name')->comment('Название товара');
                $table->string('article')->nullable()->unique()->comment('Артикул');
                $table->text('description')->nullable()->comment('Описание товара');
                $table->string('unit')->default('шт')->comment('Единица измерения (шт, кг, м, л и т.д.)');
                $table->decimal('quantity', 10, 3)->default(0)->comment('Количество на складе');
                $table->decimal('reserved_quantity', 10, 3)->default(0)->comment('Зарезервированное количество');
                $table->decimal('min_quantity', 10, 3)->default(0)->comment('Минимальное количество (для уведомлений)');
                $table->decimal('price', 10, 2)->default(0)->comment('Цена за единицу');
                $table->string('location')->nullable()->comment('Местоположение на складе');
                $table->boolean('is_active')->default(true)->comment('Активен ли товар');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_items');
    }
};
