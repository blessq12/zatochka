<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_movements', function (Blueprint $table): void {
            $table->unsignedBigInteger('reverses_movement_id')->nullable()->after('order_item_id');
            $table->index('reverses_movement_id');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_movements', function (Blueprint $table): void {
            $table->dropIndex(['reverses_movement_id']);
            $table->dropColumn('reverses_movement_id');
        });
    }
};
