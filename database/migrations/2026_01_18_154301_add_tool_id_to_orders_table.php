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
        // Не добавляем tool_id - используем связь hasMany через sharpening_tools
        // Один заказ может иметь несколько инструментов
    }

    public function down(): void
    {
        //
    }
};
