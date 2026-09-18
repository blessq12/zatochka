<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_additionals', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('phone', 32)->nullable()->after('name');
            $table->date('birthday')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('profile_additionals', function (Blueprint $table) {
            $table->dropColumn(['name', 'phone', 'birthday']);
        });
    }
};
