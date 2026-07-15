<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_equipment', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('client_id');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('client_id');
        });

        Schema::create('equipment_components', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('equipment_id');
            $table->string('name');
            $table->string('serial_number')->nullable();
            $table->foreign('equipment_id')->references('id')->on('client_equipment')->cascadeOnDelete();
        });

        Schema::create('repair_history', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('equipment_id');
            $table->unsignedBigInteger('order_item_id');
            $table->string('summary');
            $table->timestamp('recorded_at');
            $table->foreign('equipment_id')->references('id')->on('client_equipment')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_history');
        Schema::dropIfExists('equipment_components');
        Schema::dropIfExists('client_equipment');
    }
};
