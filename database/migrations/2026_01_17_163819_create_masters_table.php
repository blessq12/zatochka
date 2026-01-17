<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('masters')) {
            Schema::create('masters', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('surname')->nullable();
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->string('telegram_username')->nullable();
                $table->boolean('notifications_enabled')->default(true);
                $table->timestamp('telegram_verified_at')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->boolean('is_deleted')->default(false);
                $table->string('remember_token', 100)->nullable();
                $table->timestamps();

                $table->index('is_deleted');
                $table->index('email');
                $table->index('phone');
                $table->index('telegram_username');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('masters');
    }
};
