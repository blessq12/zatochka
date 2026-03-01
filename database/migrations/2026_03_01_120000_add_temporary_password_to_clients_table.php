<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('temporary_password')->nullable()->after('remember_token');
            $table->boolean('temporary_password_used')->default(false)->after('temporary_password');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['temporary_password', 'temporary_password_used']);
        });
    }
};
