<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('name');
            $table->decimal('qty_on_hand', 12, 3)->default(0);
            $table->string('unit', 32);
            $table->timestamps();

            $table->index('category');
        });

        Schema::create('order_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->timestamps();
        });

        Schema::create('order_issue_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained('order_issues')->cascadeOnDelete();
            $table->unsignedBigInteger('stock_item_id');
            $table->decimal('qty', 12, 3);
            $table->timestamps();

            $table->unique(['issue_id', 'stock_item_id']);
            $table->index('stock_item_id');
        });

        Schema::create('order_pricing_material_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_id')->constrained('order_pricings')->cascadeOnDelete();
            $table->unsignedBigInteger('stock_item_id');
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['pricing_id', 'stock_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_pricing_material_lines');
        Schema::dropIfExists('order_issue_lines');
        Schema::dropIfExists('order_issues');
        Schema::dropIfExists('stock_items');
    }
};
