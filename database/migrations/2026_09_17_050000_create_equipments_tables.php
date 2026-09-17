<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('name');
            $table->string('brand');
            $table->string('type');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('equipment_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnDelete();
            $table->string('name');
            $table->string('serial_number');
            $table->timestamps();

            $table->unique(['equipment_id', 'serial_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_modules');
        Schema::dropIfExists('equipments');
    }
};
