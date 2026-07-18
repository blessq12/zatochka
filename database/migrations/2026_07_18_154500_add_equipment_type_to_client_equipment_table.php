<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('client_equipment')) {
            return;
        }

        if (! Schema::hasColumn('client_equipment', 'equipment_type')) {
            Schema::table('client_equipment', function (Blueprint $table) {
                $table->string('equipment_type')->nullable()->after('model_name');
            });
        }

        DB::table('client_equipment')
            ->whereNull('equipment_type')
            ->orWhere('equipment_type', '')
            ->update(['equipment_type' => 'other']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('client_equipment')) {
            return;
        }

        if (Schema::hasColumn('client_equipment', 'equipment_type')) {
            Schema::table('client_equipment', function (Blueprint $table) {
                $table->dropColumn('equipment_type');
            });
        }
    }
};
