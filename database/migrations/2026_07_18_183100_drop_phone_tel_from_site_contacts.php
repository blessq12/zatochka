<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_contacts')
            || ! Schema::hasColumn('site_contacts', 'phone_tel')) {
            return;
        }

        Schema::table('site_contacts', function (Blueprint $table) {
            $table->dropColumn('phone_tel');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('site_contacts')
            || Schema::hasColumn('site_contacts', 'phone_tel')) {
            return;
        }

        Schema::table('site_contacts', function (Blueprint $table) {
            $table->string('phone_tel')->nullable()->after('phone');
        });
    }
};
