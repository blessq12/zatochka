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
            $table->unsignedBigInteger('order_item_id')->unique();
            $table->string('status');
            $table->unsignedBigInteger('master_id')->nullable();
            $table->timestamps();
            $table->index('status');
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

        Schema::create('master_comments', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('production_task_id');
            $table->unsignedBigInteger('master_id');
            $table->text('text');
            $table->timestamp('created_at');
            $table->foreign('production_task_id')->references('id')->on('production_tasks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_comments');
        Schema::dropIfExists('work_executions');
        Schema::dropIfExists('diagnoses');
        Schema::dropIfExists('production_tasks');
    }
};
