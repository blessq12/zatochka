<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bonus_transactions')) {
            Schema::create('bonus_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained()->onDelete('cascade');
                $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
                $table->enum('type', ['earn', 'spend']);
                $table->integer('amount');
                $table->text('description');
                $table->string('idempotency_key')->nullable()->unique();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_transactions');
    }
};
