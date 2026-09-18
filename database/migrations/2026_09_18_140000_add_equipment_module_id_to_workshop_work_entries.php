<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workshop_work_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('equipment_module_id')->nullable()->after('position');
            $table->index('equipment_module_id');
        });
    }

    public function down(): void
    {
        Schema::table('workshop_work_entries', function (Blueprint $table) {
            $table->dropIndex(['equipment_module_id']);
            $table->dropColumn('equipment_module_id');
        });
    }
};
