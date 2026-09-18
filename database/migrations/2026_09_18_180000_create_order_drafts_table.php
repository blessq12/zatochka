<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('status');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('full_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('service_type');
            $table->json('payload');
            $table->boolean('needs_delivery')->default(false);
            $table->string('delivery_address')->nullable();
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('source');
            $table->index('client_id');
            $table->index('phone');
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_drafts');
    }
};
