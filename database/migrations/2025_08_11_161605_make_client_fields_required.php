<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Сначала заполняем NULL значения
        DB::table('clients')->whereNull('phone')->update(['phone' => 'Не указан']);
        DB::table('clients')->whereNull('telegram')->update(['telegram' => 'Не указан']);
        DB::table('clients')->whereNull('birth_date')->update(['birth_date' => '1900-01-01']);
        DB::table('clients')->whereNull('delivery_address')->update(['delivery_address' => 'Не указан']);

        // Теперь делаем поля обязательными
        Schema::table('clients', function (Blueprint $table) {
            $table->string('phone', 20)->nullable(false)->change();
            $table->string('telegram', 50)->nullable(false)->change();
            $table->date('birth_date')->nullable(false)->change();
            $table->text('delivery_address')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->change();
            $table->string('telegram', 50)->nullable()->change();
            $table->date('birth_date')->nullable()->change();
            $table->text('delivery_address')->nullable()->change();
        });
    }
};
