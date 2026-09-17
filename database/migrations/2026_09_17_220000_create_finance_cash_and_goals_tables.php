<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_entries', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->timestamp('occurred_at');
            $table->string('source');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps();

            $table->index(['occurred_at', 'type']);
            $table->unique(['source', 'order_id']);
        });

        Schema::create('earnings_goals', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->decimal('target_amount', 12, 2);
            $table->date('starts_at');
            $table->dateTime('ends_at');
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earnings_goals');
        Schema::dropIfExists('cash_entries');
    }
};
