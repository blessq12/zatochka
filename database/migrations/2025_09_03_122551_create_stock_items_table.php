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
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('stock_categories')->onDelete('restrict');
            $table->string('name');
            $table->string('sku', 100)->unique(); // Уникальный код товара
            $table->text('description')->nullable();

            // Цены
            $table->decimal('purchase_price', 10, 2)->nullable(); // Закупочная цена
            $table->decimal('retail_price', 10, 2)->nullable();   // Розничная цена

            // Остатки
            $table->integer('quantity')->default(0);
            $table->integer('min_stock')->default(0);
            $table->string('unit', 20)->default('шт'); // "шт", "кг", "м"

            // Метаданные
            $table->string('supplier')->nullable(); // Поставщик
            $table->string('manufacturer')->nullable(); // Производитель
            $table->string('model')->nullable(); // Модель/артикул

            // Статус
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            // Индексы
            $table->index(['warehouse_id', 'category_id']);
            $table->index(['warehouse_id', 'is_active']);
            $table->index(['category_id', 'is_active']);
            $table->index('sku');
            $table->index('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
