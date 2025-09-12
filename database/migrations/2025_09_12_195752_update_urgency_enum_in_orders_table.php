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
        // Меняем enum на varchar - enum на уровне домена достаточно
        DB::statement("ALTER TABLE orders MODIFY COLUMN urgency VARCHAR(20) DEFAULT 'normal'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Возвращаем старый enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN urgency ENUM('normal', 'urgent') DEFAULT 'normal'");
    }
};
