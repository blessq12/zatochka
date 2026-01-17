<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_item_id')->constrained('stock_items')->onDelete('cascade');
                $table->string('part_name')->nullable();
                $table->string('part_sku')->nullable();
                $table->decimal('part_purchase_price', 10, 2)->nullable();
                $table->decimal('part_retail_price', 10, 2)->nullable();
                $table->string('part_unit', 20)->nullable();
                $table->string('part_supplier')->nullable();
                $table->string('part_manufacturer')->nullable();
                $table->string('part_model')->nullable();
                $table->enum('movement_type', ['in', 'out', 'transfer', 'adjustment', 'return']);
                $table->integer('quantity');
                $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
                $table->foreignId('repair_id')->nullable()->constrained('works')->onDelete('set null');
                $table->string('supplier')->nullable();
                $table->decimal('unit_price', 10, 2)->nullable();
                $table->decimal('total_amount', 10, 2)->nullable();
                $table->text('description')->nullable();
                $table->string('reference_number', 100)->nullable();
                $table->timestamp('movement_date')->useCurrent();
                $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
                $table->timestamps();

                $table->index(['stock_item_id', 'movement_date']);
                $table->index(['order_id', 'movement_date']);
                $table->index(['repair_id', 'movement_date']);
                $table->index('movement_type');
                $table->index('movement_date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
