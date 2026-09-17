<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('master_id')->nullable();
            $table->string('billing_type');
            $table->string('urgency');
            $table->string('status');
            $table->timestamps();

            $table->index('client_id');
            $table->index('master_id');
            $table->index('status');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('kind');
            $table->string('title')->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->text('problem')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index('equipment_id');
        });

        Schema::create('order_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->unsignedBigInteger('client_id');
            $table->unsignedTinyInteger('rating');
            $table->text('text')->nullable();
            $table->timestamps();

            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_reviews');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
