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
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('master_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('pending');
            $table->text('description')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('work_performed')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('estimated_completion')->nullable();
            $table->json('parts_used')->nullable();
            $table->json('additional_data')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'master_id']);
            $table->index(['order_id', 'status']);
            $table->index('estimated_completion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
