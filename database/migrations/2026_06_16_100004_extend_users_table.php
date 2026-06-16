<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('surname')->nullable()->after('name');
            $table->string('phone')->nullable()->unique()->after('email');
            $table->string('telegram_username')->nullable();
            $table->boolean('notifications_enabled')->default(true);
            $table->timestamp('telegram_verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'surname',
                'phone',
                'telegram_username',
                'notifications_enabled',
                'telegram_verified_at',
            ]);
        });
    }
};
