<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_items')) {
            Schema::create('stock_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained('stock_categories')->onDelete('cascade');
                $table->string('name');
                $table->string('sku', 100)->unique();
                $table->text('description')->nullable();
                $table->decimal('purchase_price', 10, 2)->nullable();
                $table->decimal('retail_price', 10, 2)->nullable();
                $table->integer('quantity')->default(0);
                $table->integer('min_stock')->default(0);
                $table->string('unit', 20)->default('шт');
                $table->string('supplier')->nullable();
                $table->string('manufacturer')->nullable();
                $table->string('model')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_deleted')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['category_id', 'is_active']);
                $table->index('sku');
                $table->index('is_deleted');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
