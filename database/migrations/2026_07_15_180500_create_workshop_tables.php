<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('order_id', 32)->unique();
            $table->string('status');
            $table->unsignedBigInteger('master_id')->nullable();
            $table->json('master_comments')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });

        Schema::create('diagnoses', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('production_task_id')->unique();
            $table->string('summary');
            $table->text('technical_notes')->nullable();
            $table->timestamp('completed_at');
            $table->foreign('production_task_id')->references('id')->on('production_tasks')->cascadeOnDelete();
        });

        Schema::create('work_executions', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('production_task_id')->unique();
            $table->string('description');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->foreign('production_task_id')->references('id')->on('production_tasks')->cascadeOnDelete();
        });

        Schema::create('performed_works', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('production_task_id');
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('equipment_component_id')->nullable();
            $table->unsignedBigInteger('master_id');
            $table->text('description');
            $table->timestamp('created_at');
            $table->foreign('production_task_id')->references('id')->on('production_tasks')->cascadeOnDelete();
            $table->foreign('order_item_id')->references('id')->on('order_items')->cascadeOnDelete();
            $table->foreign('equipment_component_id')->references('id')->on('equipment_components')->nullOnDelete();
            $table->index('order_item_id');
            $table->index('equipment_component_id');
            $table->index('production_task_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performed_works');
        Schema::dropIfExists('work_executions');
        Schema::dropIfExists('diagnoses');
        Schema::dropIfExists('production_tasks');
    }
};
