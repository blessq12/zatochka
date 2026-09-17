<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_pricings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('order_pricing_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_id')->constrained('order_pricings')->cascadeOnDelete();
            $table->unsignedBigInteger('order_item_id');
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['pricing_id', 'order_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_pricing_lines');
        Schema::dropIfExists('order_pricings');
    }
};
