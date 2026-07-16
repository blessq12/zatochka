<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            DB::table('orders')
                ->where('status', 'awaiting_pricing')
                ->update(['status' => 'works_completed']);

            if (! Schema::hasColumn('orders', 'manager_rework_comment')) {
                Schema::table('orders', function (Blueprint $table): void {
                    $table->text('manager_rework_comment')->nullable()->after('internal_notes');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            DB::table('orders')
                ->where('status', 'works_completed')
                ->update(['status' => 'awaiting_pricing']);

            if (Schema::hasColumn('orders', 'manager_rework_comment')) {
                Schema::table('orders', function (Blueprint $table): void {
                    $table->dropColumn('manager_rework_comment');
                });
            }
        }
    }
};
