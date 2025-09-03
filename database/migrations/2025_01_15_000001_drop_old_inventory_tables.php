<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Удаляем старые таблицы склада для перехода на новую архитектуру
     */
    public function up(): void
    {
        // Удаляем таблицу транзакций (сначала, так как есть внешние ключи)
        Schema::dropIfExists('inventory_transactions');

        // Удаляем таблицу товаров
        Schema::dropIfExists('inventory_items');
    }

    /**
     * Восстанавливаем старые таблицы (для отката)
     */
    public function down(): void
    {
        // Восстанавливаем таблицу товаров
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->integer('quantity')->default(0);
            $table->string('unit');
            $table->integer('min_stock')->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        // Восстанавливаем таблицу транзакций
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->integer('quantity');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }
};
