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
            $table->boolean('is_paid')->default(false)->after('total_tools_count');
            $table->boolean('is_ready_for_pickup')->default(false)->after('is_paid');
            $table->boolean('quality_survey_sent')->default(false)->after('is_ready_for_pickup');
            $table->boolean('review_request_sent')->default(false)->after('quality_survey_sent');
            $table->timestamp('ready_at')->nullable()->after('review_request_sent');
            $table->timestamp('paid_at')->nullable()->after('ready_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'is_paid',
                'is_ready_for_pickup',
                'quality_survey_sent',
                'review_request_sent',
                'ready_at',
                'paid_at'
            ]);
        });
    }
};
