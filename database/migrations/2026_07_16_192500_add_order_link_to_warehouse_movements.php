<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_movements', function (Blueprint $table): void {
            $table->string('order_id', 32)->nullable()->after('comment');
            $table->unsignedBigInteger('order_item_id')->nullable()->after('order_id');
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_movements', function (Blueprint $table): void {
            $table->dropIndex(['order_id']);
            $table->dropColumn(['order_id', 'order_item_id']);
        });
    }
};
