<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('performed_works', 'equipment_component_id')) {
            return;
        }

        Schema::table('performed_works', function (Blueprint $table) {
            $table->unsignedBigInteger('equipment_component_id')->nullable()->after('order_item_id');
            $table->foreign('equipment_component_id')
                ->references('id')
                ->on('equipment_components')
                ->nullOnDelete();
            $table->index('equipment_component_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('performed_works', 'equipment_component_id')) {
            return;
        }

        Schema::table('performed_works', function (Blueprint $table) {
            $table->dropForeign(['equipment_component_id']);
            $table->dropIndex(['equipment_component_id']);
            $table->dropColumn('equipment_component_id');
        });
    }
};
