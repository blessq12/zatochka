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
        Schema::table('orders', function (Blueprint $table) {
            // Сначала удаляем foreign key constraint для master_id
            $table->dropForeign(['master_id']);

            // Удаляем старые поля цен и мастера
            $table->dropColumn([
                'total_amount',
                'final_price',
                'cost_price',
                'profit',
                'master_id',
                'is_paid',
                'paid_at'
            ]);

            // Добавляем новые поля цен
            $table->decimal('estimated_price', 10, 2)->nullable()->comment('Ориентировочная цена при приеме');
            $table->decimal('actual_price', 10, 2)->nullable()->comment('Фактическая стоимость после ремонта');

            // Меняем type и status на обычные строки
            $table->string('type')->change();
            $table->string('status')->default('new')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Возвращаем старые поля
            $table->bigInteger('master_id')->unsigned()->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->decimal('final_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('profit', 10, 2)->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();

            // Восстанавливаем foreign key для master_id
            $table->foreign('master_id')->references('id')->on('users');

            // Возвращаем enum для type и status
            $table->enum('type', [
                'repair',
                'sharpening',
                'diagnostic',
                'replacement',
                'maintenance',
                'consultation',
                'warranty'
            ])->change();

            $table->enum('status', [
                'new',
                'consultation',
                'diagnostic',
                'in_work',
                'waiting_parts',
                'ready',
                'issued',
                'cancelled'
            ])->default('new')->change();

            // Удаляем новые поля
            $table->dropColumn(['estimated_price', 'actual_price']);
        });
    }
};
