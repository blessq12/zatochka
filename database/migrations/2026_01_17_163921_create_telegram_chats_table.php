<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('telegram_chats')) {
            Schema::create('telegram_chats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
                $table->string('username');
                $table->bigInteger('chat_id')->unique();
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->boolean('is_deleted')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_chats');
    }
};
