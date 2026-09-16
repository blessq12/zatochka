<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('repair_history');

        DB::table('entity_id_sequences')->where('name', 'repair_history')->delete();
    }

    public function down(): void
    {
        Schema::create('repair_history', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('equipment_id');
            $table->unsignedBigInteger('order_item_id');
            $table->string('summary');
            $table->timestamp('recorded_at');
            $table->timestamps();
        });
    }
};
