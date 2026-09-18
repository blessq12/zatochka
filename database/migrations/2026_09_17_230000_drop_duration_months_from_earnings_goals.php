<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('earnings_goals')) {
            return;
        }
        if (! Schema::hasColumn('earnings_goals', 'duration_months')) {
            return;
        }

        Schema::table('earnings_goals', function (Blueprint $table) {
            $table->dropColumn('duration_months');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('earnings_goals')) {
            return;
        }
        if (Schema::hasColumn('earnings_goals', 'duration_months')) {
            return;
        }

        Schema::table('earnings_goals', function (Blueprint $table) {
            $table->unsignedTinyInteger('duration_months')->default(1)->after('starts_at');
        });
    }
};
