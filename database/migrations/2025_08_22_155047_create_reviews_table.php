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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // feedback/testimonial
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedBigInteger('entity_id')->nullable(); // ID связанной сущности
            $table->string('entity_type')->nullable(); // тип связанной сущности
            $table->integer('rating')->nullable(); // 1-5
            $table->text('comment');
            $table->string('source'); // website/telegram/api/external
            $table->string('status')->default('pending'); // pending/approved/rejected
            $table->text('reply')->nullable(); // ответ на отзыв
            $table->json('metadata')->nullable(); // дополнительные данные
            $table->timestamps();

            // Индексы для быстрого поиска
            $table->index(['type', 'status']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['order_id']);
            $table->index(['user_id']);
            $table->index(['source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
