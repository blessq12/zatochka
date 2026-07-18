<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('client_equipment')) {
            return;
        }

        if (! Schema::hasColumn('client_equipment', 'notes')) {
            return;
        }

        Schema::table('client_equipment', function (Blueprint $table): void {
            $table->dropColumn('notes');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('client_equipment')) {
            return;
        }

        if (Schema::hasColumn('client_equipment', 'notes')) {
            return;
        }

        Schema::table('client_equipment', function (Blueprint $table): void {
            $table->text('notes')->nullable();
        });
    }
};
