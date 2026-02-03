<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table) {
            if (Schema::hasColumn('works', 'work_time_minutes')) {
                $table->dropColumn('work_time_minutes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->integer('work_time_minutes')->nullable()->after('materials_cost');
        });
    }
};
