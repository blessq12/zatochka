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
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->enum('work_type', ['sharpening', 'repair', 'diagnostic'])->default('repair')->comment('Тип работы');
            $table->integer('quantity')->nullable()->comment('Количество инструментов (для заточки)');
            $table->decimal('unit_price', 10, 2)->nullable()->comment('Цена за единицу (для заточки)');
            $table->text('description')->comment('Описание работы');
            $table->decimal('work_price', 10, 2)->comment('Стоимость работы');
            $table->decimal('materials_cost', 10, 2)->nullable()->comment('Стоимость материалов/расходников');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};
