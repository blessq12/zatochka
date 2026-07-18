<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_operations', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('type');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('cash_operations', function (Blueprint $table) {
            $table->dropIndex(['payment_method']);
            $table->dropColumn('payment_method');
        });
    }
};
