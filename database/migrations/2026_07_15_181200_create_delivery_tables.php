<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('order_id', 32);
            $table->string('status');
            $table->boolean('pickup')->default(false);
            $table->string('city');
            $table->string('street');
            $table->string('building');
            $table->string('apartment')->nullable();
            $table->string('comment')->nullable();
            $table->unsignedBigInteger('courier_id')->nullable();
            $table->timestamp('courier_assigned_at')->nullable();
            $table->timestamps();
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_requests');
    }
};
