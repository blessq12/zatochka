<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshop_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->unsignedBigInteger('master_id');
            $table->string('status');
            $table->timestamps();

            $table->index('master_id');
            $table->index('status');
        });

        Schema::create('workshop_item_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('workshop_jobs')->cascadeOnDelete();
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedInteger('completed_qty')->nullable();
            $table->timestamps();

            $table->unique(['job_id', 'order_item_id']);
        });

        Schema::create('workshop_work_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_work_id')->constrained('workshop_item_works')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_work_entries');
        Schema::dropIfExists('workshop_item_works');
        Schema::dropIfExists('workshop_jobs');
    }
};
