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
            // Обновляем enum tool_type чтобы включить значения для ремонта
            $table->enum('tool_type', [
                // Значения для заточки
                'manicure',
                'hairdresser_groomer_barber',
                'lashmaker_browmaker_tweezers',
                'manicure_podology_apparatus',
                // Значения для ремонта
                'clipper',
                'dryer',
                'scissors',
                'trimmer',
                'ultrasonic',
                'other'
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Возвращаем старые значения
            $table->enum('tool_type', [
                'manicure',
                'hairdresser_groomer_barber',
                'lashmaker_browmaker_tweezers',
                'manicure_podology_apparatus'
            ])->change();
        });
    }
};
