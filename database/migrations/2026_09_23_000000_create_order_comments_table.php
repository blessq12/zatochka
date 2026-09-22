<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('author_type');
            $table->unsignedBigInteger('author_id');
            $table->text('body');
            $table->string('kind')->default('regular');
            $table->timestamps();

            $table->index('order_id');
            $table->index(['author_type', 'author_id']);
            $table->index('kind');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_comments');
    }
};
