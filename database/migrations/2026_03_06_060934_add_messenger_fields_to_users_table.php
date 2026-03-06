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
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_username')->nullable();
            $table->timestamp('telegram_verified_at')->nullable();
            $table->string('max_username')->nullable();
            $table->timestamp('max_verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_username',
                'telegram_verified_at',
                'max_username',
                'max_verified_at',
            ]);
        });
    }
};
