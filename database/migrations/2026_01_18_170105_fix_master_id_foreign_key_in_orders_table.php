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
            // Удаляем старый внешний ключ, который ссылается на users
            $table->dropForeign(['master_id']);
            
            // Создаем новый внешний ключ, который ссылается на masters
            $table->foreign('master_id')
                ->references('id')
                ->on('masters')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Удаляем внешний ключ на masters
            $table->dropForeign(['master_id']);
            
            // Восстанавливаем старый внешний ключ на users
            $table->foreign('master_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
