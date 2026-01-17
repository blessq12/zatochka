<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('snapshots')) {
            Schema::create('snapshots', function (Blueprint $table) {
                $table->id();
                $table->char('aggregate_uuid', 36);
                $table->unsignedBigInteger('aggregate_version');
                $table->json('state');
                $table->timestamps();

                $table->index('aggregate_uuid');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('snapshots');
    }
};
