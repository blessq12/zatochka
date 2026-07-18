<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable()->unique()->after('role');
            $table->boolean('requires_password_set')->default(false)->after('password');
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
        });

        Schema::table('clients', function (Blueprint $table): void {
            $table->date('birth_date')->nullable()->after('email');
            $table->string('delivery_address')->nullable()->after('birth_date');
        });

        Schema::create('client_leads', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('client_id');
            $table->json('service_types');
            $table->text('comment')->nullable();
            $table->json('intake_data')->nullable();
            $table->boolean('needs_delivery')->default(false);
            $table->string('delivery_address')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_leads');

        Schema::table('clients', function (Blueprint $table): void {
            $table->dropColumn(['birth_date', 'delivery_address']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['client_id', 'requires_password_set']);
        });
    }
};
