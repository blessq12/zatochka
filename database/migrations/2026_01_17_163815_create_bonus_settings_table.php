<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bonus_settings')) {
            Schema::create('bonus_settings', function (Blueprint $table) {
                $table->id();
                $table->timestamp('updated_at');
                $table->integer('birthday_bonus')->default(0)->comment('Бонус на день рождения');
                $table->integer('first_order_bonus')->default(0)->comment('Бонус за первый заказ');
                $table->decimal('rate', 8, 2)->default(1.00)->comment('Курс: 1 бонус = X рублей');
                $table->decimal('percent_per_order', 5, 2)->default(5.00)->comment('Процент начислений с заказа');
                $table->decimal('min_order_sum_for_spending', 10, 2)->default(1000.00)->comment('Минимальная сумма заказа для списания бонусов');
                $table->integer('expire_days')->default(365)->comment('Срок действия бонусов в днях');
                $table->decimal('min_order_amount', 10, 2)->default(100.00)->comment('Минимальная сумма заказа для начисления');
                $table->integer('max_bonus_per_order')->default(1000)->comment('Максимальные бонусы за один заказ');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_settings');
    }
};
