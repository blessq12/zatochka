<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('tool_id')->constrained()->onDelete('cascade');
            $table->text('problem_description')->nullable();
            $table->text('work_description')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'tool_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tools');
    }
};
