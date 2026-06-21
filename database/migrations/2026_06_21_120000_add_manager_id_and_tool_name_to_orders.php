<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('manager_id')
                ->nullable()
                ->after('master_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('order_tools', function (Blueprint $table) {
            $table->string('name')->nullable()->after('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_tools', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manager_id');
        });
    }
};
