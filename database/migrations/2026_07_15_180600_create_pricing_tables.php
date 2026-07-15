<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimates', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('order_item_id')->unique();
            $table->decimal('estimated_amount', 12, 2);
            $table->string('currency', 3)->default('RUB');
            $table->boolean('calculated')->default(false);
            $table->timestamps();
        });

        Schema::create('item_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('estimate_id')->unique();
            $table->unsignedBigInteger('order_item_id');
            $table->decimal('base_amount', 12, 2);
            $table->string('currency', 3)->default('RUB');
            $table->decimal('final_amount', 12, 2)->nullable();
            $table->foreign('estimate_id')->references('id')->on('estimates')->cascadeOnDelete();
        });

        Schema::create('discounts', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('item_price_id')->unique();
            $table->string('type');
            $table->decimal('value', 12, 2);
            $table->string('reason')->nullable();
            $table->foreign('item_price_id')->references('id')->on('item_prices')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
        Schema::dropIfExists('item_prices');
        Schema::dropIfExists('estimates');
    }
};
