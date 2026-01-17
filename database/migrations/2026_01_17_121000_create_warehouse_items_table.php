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
        Schema::create('warehouse_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_category_id')->nullable()->constrained('warehouse_categories')->onDelete('set null');
            $table->string('name');
            $table->string('article')->nullable();
            $table->text('description')->nullable();
            $table->string('unit', 20)->default('шт');
            $table->decimal('quantity', 10, 3)->default(0);
            $table->decimal('reserved_quantity', 10, 3)->default(0);
            $table->decimal('min_quantity', 10, 3)->default(0);
            $table->decimal('price', 10, 2)->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('warehouse_category_id');
            $table->index('article');
            $table->index('is_active');
            $table->index(['warehouse_category_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_items');
    }
};
