<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_items') || ! Schema::hasColumn('order_items', 'client_equipment_id')) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_equipment_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Irreversible for greenfield sharpening orders with null equipment.
    }
};
