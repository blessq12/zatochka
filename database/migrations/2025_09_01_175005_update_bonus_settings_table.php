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
        Schema::table('bonus_settings', function (Blueprint $table) {
            // Удаляем старые поля
            $table->dropColumn([
                'earn_percent',
                'expire_days',
                'min_order_amount',
                'max_bonus_per_order',
            ]);

            // Добавляем новые поля
            $table->integer('birthday_bonus')->default(0)->comment('Бонус на день рождения');
            $table->integer('first_order_bonus')->default(0)->comment('Бонус за первый заказ');
            $table->decimal('rate', 8, 2)->default(1.00)->comment('Курс: 1 бонус = X рублей');
            $table->decimal('percent_per_order', 5, 2)->default(5.00)->comment('Процент начислений с заказа');
            $table->decimal('min_order_sum_for_spending', 10, 2)->default(1000.00)->comment('Минимальная сумма заказа для списания бонусов');
            $table->integer('expire_days')->default(365)->comment('Срок действия бонусов в днях');
            $table->decimal('min_order_amount', 10, 2)->default(100.00)->comment('Минимальная сумма заказа для начисления');
            $table->integer('max_bonus_per_order')->default(1000)->comment('Максимальные бонусы за один заказ');

            // Добавляем created_at (updated_at уже есть)
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bonus_settings', function (Blueprint $table) {
            // Удаляем новые поля
            $table->dropColumn([
                'birthday_bonus',
                'first_order_bonus',
                'rate',
                'percent_per_order',
                'min_order_sum_for_spending',
                'expire_days',
                'min_order_amount',
                'max_bonus_per_order',
                'created_at',
            ]);

            // Возвращаем старые поля
            $table->decimal('earn_percent', 5, 2);
            $table->integer('expire_days');
            $table->decimal('min_order_amount', 10, 2);
            $table->integer('max_bonus_per_order');
            $table->timestamp('updated_at');
        });
    }
};
