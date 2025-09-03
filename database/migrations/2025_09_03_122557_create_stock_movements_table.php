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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->enum('movement_type', ['in', 'out', 'transfer', 'adjustment', 'return']);
            $table->integer('quantity');

            // Связи
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('repair_id')->nullable()->constrained()->onDelete('set null');
            $table->string('supplier')->nullable(); // Поставщик (если приход)

            // Цены
            $table->decimal('unit_price', 10, 2)->nullable(); // Цена за единицу на момент движения
            $table->decimal('total_amount', 10, 2)->nullable(); // Общая сумма

            // Метаданные
            $table->text('description')->nullable();
            $table->string('reference_number', 100)->nullable(); // Номер накладной/счёта
            $table->timestamp('movement_date')->useCurrent();

            // Аудит
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            // Индексы
            $table->index(['stock_item_id', 'movement_date']);
            $table->index(['warehouse_id', 'movement_date']);
            $table->index(['order_id', 'movement_date']);
            $table->index(['repair_id', 'movement_date']);
            $table->index('movement_type');
            $table->index('movement_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
