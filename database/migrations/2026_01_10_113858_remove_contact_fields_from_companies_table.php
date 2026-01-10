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
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['phone', 'email', 'website']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Восстанавливаем поля в правильном порядке согласно исходным миграциям
            $table->string('website')->nullable()->after('legal_address');
            $table->string('phone')->nullable()->after('website');
            $table->string('email')->nullable()->after('phone');
        });
    }
};
