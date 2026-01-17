<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_work_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->constrained('works')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('warehouse_item_id')->nullable()->constrained('warehouse_items')->onDelete('set null');
            
            // Snapshot данных материала на момент списания
            $table->string('name');
            $table->string('article')->nullable();
            $table->string('category_name')->nullable();
            $table->string('unit', 20)->default('шт');
            $table->decimal('price', 10, 2);
            $table->decimal('quantity', 10, 3);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['work_id', 'order_id']);
            $table->index('warehouse_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_work_materials');
    }
};
