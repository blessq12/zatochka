<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonus_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('earn_percent', 5, 2);
            $table->integer('expire_days');
            $table->decimal('min_order_amount', 10, 2);
            $table->integer('max_bonus_per_order');
            $table->timestamp('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_settings');
    }
};
