<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('unit');
            $table->string('category');
            $table->timestamps();
            $table->index('category');
        });

        Schema::create('stock_items', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('material_id')->unique();
            $table->decimal('quantity_on_hand', 14, 3)->default(0);
            $table->timestamps();
            $table->foreign('material_id')->references('id')->on('materials')->cascadeOnDelete();
        });

        Schema::create('warehouse_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('stock_item_id');
            $table->string('type');
            $table->decimal('quantity', 14, 3);
            $table->string('comment')->nullable();
            $table->timestamp('occurred_at');
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_movements');
        Schema::dropIfExists('stock_items');
        Schema::dropIfExists('materials');
    }
};
