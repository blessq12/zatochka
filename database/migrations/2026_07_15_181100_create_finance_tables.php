<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('order_id', 32);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('RUB');
            $table->string('method');
            $table->timestamp('accepted_at');
            $table->timestamps();
            $table->index('order_id');
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('payment_id');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('RUB');
            $table->string('reason')->nullable();
            $table->timestamp('created_at');
            $table->foreign('payment_id')->references('id')->on('payments')->cascadeOnDelete();
        });

        Schema::create('cash_operations', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('RUB');
            $table->string('comment')->nullable();
            $table->timestamp('registered_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_operations');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payments');
    }
};
