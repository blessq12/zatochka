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
            // Добавляем поля для связи с типами
            $table->string('payment_type')->nullable()->after('status');
            $table->string('delivery_type')->nullable()->after('payment_type');

            // Добавляем индексы для быстрого поиска
            $table->index('payment_type');
            $table->index('delivery_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['payment_type']);
            $table->dropIndex(['delivery_type']);
            $table->dropColumn(['payment_type', 'delivery_type']);
        });
    }
};
