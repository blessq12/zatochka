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
            // Изменяем tool_type с enum на string для динамических значений
            $table->string('tool_type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Возвращаем enum ограничения
            $table->enum('tool_type', [
                'manicure',
                'hairdresser_groomer_barber',
                'lashmaker_browmaker_tweezers',
                'manicure_podology_apparatus',
                'clipper',
                'dryer',
                'scissors',
                'trimmer',
                'ultrasonic',
                'other'
            ])->change();
        });
    }
};
