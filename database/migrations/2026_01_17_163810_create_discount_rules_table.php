<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('discount_rules')) {
            Schema::create('discount_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('type', ['percent', 'fixed']);
                $table->decimal('value', 10, 2);
                $table->json('conditions')->nullable();
                $table->timestamp('active_from')->nullable();
                $table->timestamp('active_to')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_deleted')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_rules');
    }
};
