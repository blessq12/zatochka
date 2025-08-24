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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->change();
            $table->string('telegram', 50)->nullable()->change();
            $table->date('birth_date')->nullable()->change();
            $table->text('delivery_address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('phone', 20)->nullable(false)->change();
            $table->string('telegram', 50)->nullable(false)->change();
            $table->date('birth_date')->nullable(false)->change();
            $table->text('delivery_address')->nullable(false)->change();
        });
    }
};
