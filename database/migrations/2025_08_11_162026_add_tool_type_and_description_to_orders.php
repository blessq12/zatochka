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
            $table->enum('tool_type', [
                'manicure',
                'hairdresser_groomer_barber',
                'lashmaker_browmaker_tweezers',
                'manicure_podology_apparatus'
            ])->after('service_type');
            $table->text('problem_description')->nullable()->after('tool_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tool_type', 'problem_description']);
        });
    }
};
