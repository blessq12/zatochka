<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_additionals', function (Blueprint $table) {
            $table->string('delivery_address')->nullable()->after('birthday');
        });
    }

    public function down(): void
    {
        Schema::table('profile_additionals', function (Blueprint $table) {
            $table->dropColumn('delivery_address');
        });
    }
};
