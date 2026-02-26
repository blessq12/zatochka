<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('marketing_source')->nullable()->after('telegram_verified_at');
            $table->string('first_contact_channel')->nullable()->after('marketing_source');
            $table->text('first_contact_notes')->nullable()->after('first_contact_channel');
            $table->text('marketing_notes')->nullable()->after('first_contact_notes');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'marketing_source',
                'first_contact_channel',
                'first_contact_notes',
                'marketing_notes',
            ]);
        });
    }
};

