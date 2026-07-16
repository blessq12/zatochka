<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_prices', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('performed_work_id')->unique();
            $table->unsignedBigInteger('order_item_id');
            $table->decimal('base_amount', 12, 2);
            $table->string('currency', 3)->default('RUB');
            $table->decimal('final_amount', 12, 2)->nullable();
            $table->boolean('calculated')->default(false);
            $table->foreign('performed_work_id')->references('id')->on('performed_works')->cascadeOnDelete();
            $table->index('order_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_prices');
    }
};
