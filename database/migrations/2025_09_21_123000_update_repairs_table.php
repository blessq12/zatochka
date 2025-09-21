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
        Schema::table('repairs', function (Blueprint $table) {
            // Добавляем новые поля
            $table->text('comments')->nullable()->comment('Комментарии к ремонту');
            $table->json('completed_works')->nullable()->comment('Выполненные работы в JSON формате');

            // Переименовываем description в problem_description для ясности
            $table->renameColumn('description', 'problem_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            // Удаляем добавленные поля
            $table->dropColumn(['comments', 'completed_works']);

            // Возвращаем старое название
            $table->renameColumn('problem_description', 'description');
        });
    }
};
