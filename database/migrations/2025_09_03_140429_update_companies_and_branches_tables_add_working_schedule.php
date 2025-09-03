<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Добавляем поле для описания компании
            $table->text('description')->nullable()->after('legal_address');

            // Добавляем поле для контактного телефона
            $table->string('phone')->nullable()->after('website');

            // Добавляем поле для контактного email
            $table->string('email')->nullable()->after('phone');

            // Добавляем поле для статуса активности
            $table->boolean('is_active')->default(true)->after('additional_data');
        });

        Schema::table('branches', function (Blueprint $table) {
            // Заменяем текстовое поле working_hours на JSON поле working_schedule
            $table->json('working_schedule')->nullable()->after('working_hours');

            // Добавляем поле для времени открытия/закрытия (общее)
            $table->string('opening_time')->nullable()->after('working_schedule');
            $table->string('closing_time')->nullable()->after('opening_time');

            // Добавляем поле для статуса главного филиала
            $table->boolean('is_main')->default(false)->after('is_active');

            // Добавляем поле для приоритета сортировки
            $table->integer('sort_order')->default(0)->after('is_main');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['description', 'phone', 'email', 'is_active']);
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['working_schedule', 'opening_time', 'closing_time', 'is_main', 'sort_order']);
        });
    }
};
