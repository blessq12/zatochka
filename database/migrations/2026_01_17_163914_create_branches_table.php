<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('code')->unique();
                $table->text('address');
                $table->string('phone');
                $table->string('email');
                $table->text('working_hours')->nullable();
                $table->json('working_schedule')->nullable();
                $table->string('opening_time')->nullable();
                $table->string('closing_time')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_main')->default(false);
                $table->integer('sort_order')->default(0);
                $table->boolean('is_deleted')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
