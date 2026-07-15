<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('phone')->unique();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('bonus_account_id');
            $table->decimal('bonus_balance', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('client_history', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('order_id');
            $table->string('note');
            $table->timestamp('recorded_at');
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_history');
        Schema::dropIfExists('clients');
    }
};
