<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('password');
            $table->date('birth_date')->nullable();
            $table->string('delivery_address')->nullable();
            $table->boolean('requires_password_set')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('site_leads', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->json('service_types');
            $table->text('comment')->nullable();
            $table->json('intake_data')->nullable();
            $table->boolean('needs_delivery')->default(false);
            $table->string('delivery_address')->nullable();
            $table->boolean('converted')->default(false);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_leads');
        Schema::dropIfExists('clients');
    }
};
