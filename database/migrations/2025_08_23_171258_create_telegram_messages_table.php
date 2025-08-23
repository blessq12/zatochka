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
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegram_chat_id')->constrained('telegram_chats')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->bigInteger('message_id')->comment('ID сообщения в Telegram');
            $table->enum('direction', ['incoming', 'outgoing'])->comment('Направление сообщения');
            $table->enum('type', ['text', 'photo', 'document', 'audio', 'video', 'voice', 'sticker', 'command'])->default('text');
            $table->text('content')->nullable()->comment('Текст сообщения');
            $table->json('media_data')->nullable()->comment('Данные медиа файлов');
            $table->json('metadata')->nullable()->comment('Дополнительные данные');
            $table->timestamp('sent_at')->comment('Время отправки/получения');
            $table->timestamps();

            // Индексы для быстрого поиска
            $table->index(['telegram_chat_id', 'sent_at']);
            $table->index(['client_id', 'sent_at']);
            $table->index('direction');
            $table->index('type');
            $table->index('message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
    }
};
