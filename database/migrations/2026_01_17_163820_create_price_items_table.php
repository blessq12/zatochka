<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('price_items')) {
            Schema::create('price_items', function (Blueprint $table) {
                $table->id();
                $table->enum('service_type', ['sharpening', 'repair'])->comment('Тип услуги: заточка или ремонт');
                $table->string('category_title')->comment('Название категории/блока');
                $table->string('name')->comment('Название услуги');
                $table->text('description')->nullable()->comment('Описание услуги');
                $table->string('price')->comment('Цена (строка для поддержки форматов типа "800/400")');
                $table->integer('sort_order')->default(0)->comment('Порядок сортировки');
                $table->boolean('is_active')->default(true)->comment('Активна ли услуга');
                $table->timestamps();

                $table->index(['service_type', 'is_active']);
                $table->index(['service_type', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('price_items');
    }
};
