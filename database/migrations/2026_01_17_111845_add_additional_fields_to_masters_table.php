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
        Schema::table('masters', function (Blueprint $table) {
            $table->string('surname')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('telegram_username')->nullable()->after('phone');
            $table->boolean('notifications_enabled')->default(true)->after('telegram_username');

            $table->index('phone');
            $table->index('telegram_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('masters', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropIndex(['telegram_username']);
            
            $table->dropColumn([
                'surname',
                'phone',
                'telegram_username',
                'notifications_enabled',
            ]);
        });
    }
};
