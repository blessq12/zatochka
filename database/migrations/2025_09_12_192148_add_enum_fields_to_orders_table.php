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
            $table->enum('type', [
                'repair',
                'diagnostic',
                'replacement',
                'maintenance',
                'consultation',
                'warranty',
            ])->after('order_number');

            $table->enum('status', [
                'new',
                'consultation',
                'diagnostic',
                'in_work',
                'waiting_parts',
                'ready',
                'issued',
                'cancelled',
            ])->default('new')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['type', 'status']);
        });
    }
};
