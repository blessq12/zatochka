<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bonus_accounts')) {
            Schema::create('bonus_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->unique()->constrained()->onDelete('cascade');
                $table->integer('balance')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_accounts');
    }
};
