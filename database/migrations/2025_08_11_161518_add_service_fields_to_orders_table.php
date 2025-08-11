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
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('service_type', ['repair', 'maintenance', 'consultation', 'other'])->after('client_id');
            $table->json('tools_photos')->nullable()->after('service_type');
            $table->boolean('needs_consultation')->default(false)->after('tools_photos');
            $table->integer('total_tools_count')->default(0)->after('needs_consultation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['service_type', 'tools_photos', 'needs_consultation', 'total_tools_count']);
        });
    }
};
