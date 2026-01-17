<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clients')) {
            Schema::create('clients', function (Blueprint $table) {
                $table->id();
                $table->string('full_name');
                $table->string('phone')->unique();
                $table->string('telegram')->nullable();
                $table->date('birth_date')->nullable();
                $table->text('delivery_address')->nullable();
                $table->integer('bonus_points')->default(0);
                $table->string('password')->nullable();
                $table->string('remember_token', 100)->nullable();
                $table->boolean('is_deleted')->default(false);
                $table->timestamps();
                $table->string('email')->nullable();
                $table->timestamp('telegram_verified_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
