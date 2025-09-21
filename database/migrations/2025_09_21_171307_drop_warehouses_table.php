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
        // Сначала удаляем foreign key constraint из stock_movements
        if (Schema::hasTable('stock_movements') && Schema::hasColumn('stock_movements', 'warehouse_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropForeign(['warehouse_id']);
            });
        }

        // Затем удаляем таблицу warehouses
        Schema::dropIfExists('warehouses');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index(['branch_id', 'is_active']);
            $table->index('is_deleted');
        });
    }
};
