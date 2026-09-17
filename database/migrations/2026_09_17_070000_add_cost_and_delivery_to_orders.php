<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('estimated_cost', 12, 2)->nullable()->after('urgency');
            $table->boolean('needs_delivery')->default(false)->after('estimated_cost');
            $table->string('delivery_address')->nullable()->after('needs_delivery');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['estimated_cost', 'needs_delivery', 'delivery_address']);
        });
    }
};
