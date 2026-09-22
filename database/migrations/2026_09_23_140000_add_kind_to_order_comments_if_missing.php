<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_comments')) {
            return;
        }
        if (Schema::hasColumn('order_comments', 'kind')) {
            return;
        }

        Schema::table('order_comments', function (Blueprint $table) {
            $table->string('kind')->default('regular')->after('body');
            $table->index('kind');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('order_comments') || ! Schema::hasColumn('order_comments', 'kind')) {
            return;
        }

        Schema::table('order_comments', function (Blueprint $table) {
            $table->dropIndex(['kind']);
            $table->dropColumn('kind');
        });
    }
};
