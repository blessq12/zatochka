<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('materials')) {
            return;
        }

        if (! Schema::hasColumn('materials', 'category')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->string('category')->default('consumable');
                $table->index('category');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('materials') || ! Schema::hasColumn('materials', 'category')) {
            return;
        }

        Schema::table('materials', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }
};
