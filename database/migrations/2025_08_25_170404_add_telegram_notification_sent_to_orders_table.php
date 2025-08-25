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
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('telegram_notification_sent')->default(false)->after('review_request_sent');
            $table->timestamp('telegram_notification_sent_at')->nullable()->after('telegram_notification_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['telegram_notification_sent', 'telegram_notification_sent_at']);
        });
    }
};
