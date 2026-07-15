<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('order_id')->unique();
            $table->unsignedBigInteger('client_id');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->text('manager_reply')->nullable();
            $table->string('status');
            $table->unsignedBigInteger('moderated_by')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('moderated_at')->nullable();
            $table->timestamp('hidden_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            $table->index('client_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
