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
        Schema::create('telegram_chats', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique()->comment('Telegram username без @');
            $table->bigInteger('chat_id')->unique()->comment('Telegram chat_id');
            $table->string('first_name')->nullable()->comment('Имя пользователя в Telegram');
            $table->string('last_name')->nullable()->comment('Фамилия пользователя в Telegram');
            $table->boolean('is_active')->default(true)->comment('Активен ли чат');
            $table->timestamp('last_activity_at')->nullable()->comment('Последняя активность');
            $table->json('metadata')->nullable()->comment('Дополнительные данные');
            $table->timestamps();

            // Индексы для быстрого поиска
            $table->index('username');
            $table->index('chat_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_chats');
    }
};
