<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table) {
            if (Schema::hasColumn('works', 'work_type')) {
                $table->dropColumn('work_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->enum('work_type', ['sharpening', 'repair', 'diagnostic'])->default('repair')->after('order_id');
        });
    }
};
