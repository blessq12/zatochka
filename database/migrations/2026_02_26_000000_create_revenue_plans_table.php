<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('revenue_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('target_amount', 12, 2);
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();

            $table->unique(['year', 'month', 'branch_id'], 'revenue_plans_period_branch_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_plans');
    }
};

