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
        Schema::table('works', function (Blueprint $table) {
            $table->string('equipment_component_name')->nullable()->after('description')->comment('Название элемента оборудования');
            $table->string('equipment_component_serial_number')->nullable()->after('equipment_component_name')->comment('Серийный номер элемента оборудования');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->dropColumn(['equipment_component_name', 'equipment_component_serial_number']);
        });
    }
};
