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

        if (! Schema::hasColumn('client_equipment', 'brand')) {
            Schema::table('client_equipment', function (Blueprint $table) {
                $table->string('brand')->default('');
            });
        }

        if (! Schema::hasColumn('client_equipment', 'model_name')) {
            Schema::table('client_equipment', function (Blueprint $table) {
                $table->string('model_name')->default('');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('client_equipment')) {
            return;
        }

        if (Schema::hasColumn('client_equipment', 'model_name')) {
            Schema::table('client_equipment', function (Blueprint $table) {
                $table->dropColumn('model_name');
            });
        }

        if (Schema::hasColumn('client_equipment', 'brand')) {
            Schema::table('client_equipment', function (Blueprint $table) {
                $table->dropColumn('brand');
            });
        }
    }
};
