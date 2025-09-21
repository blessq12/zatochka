<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \DB::statement("ALTER TABLE orders MODIFY COLUMN type ENUM('repair','sharpening','diagnostic','replacement','maintenance','consultation','warranty') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("ALTER TABLE orders MODIFY COLUMN type ENUM('repair','diagnostic','replacement','maintenance','consultation','warranty') NOT NULL");
    }
};
