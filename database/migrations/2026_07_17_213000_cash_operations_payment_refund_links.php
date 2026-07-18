<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_operations', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_id')->nullable()->after('comment');
            $table->unsignedBigInteger('refund_id')->nullable()->after('payment_id');
            $table->unique('payment_id');
            $table->unique('refund_id');
            $table->index('registered_at');
            $table->index(['type', 'registered_at']);
            $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
            $table->foreign('refund_id')->references('id')->on('refunds')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cash_operations', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropForeign(['refund_id']);
            $table->dropUnique(['payment_id']);
            $table->dropUnique(['refund_id']);
            $table->dropIndex(['registered_at']);
            $table->dropIndex(['type', 'registered_at']);
            $table->dropColumn(['payment_id', 'refund_id']);
        });
    }
};
