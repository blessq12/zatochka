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
        Schema::table('clients', function (Blueprint $table) {
            // Поля для аутентификации
            $table->string('password')->nullable()->after('delivery_address');
            $table->timestamp('telegram_verified_at')->nullable()->after('password');
            $table->rememberToken()->after('telegram_verified_at');

            // Убеждаемся, что phone уникален для аутентификации
            $table->unique('phone', 'clients_phone_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_phone_unique');
            $table->dropColumn(['password', 'telegram_verified_at', 'remember_token']);
        });
    }
};
