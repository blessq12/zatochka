<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table): void {
                if (! Schema::hasColumn('order_items', 'rejected_quantity')) {
                    $table->unsignedInteger('rejected_quantity')->default(0)->after('quantity');
                }

                if (! Schema::hasColumn('order_items', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('rejected_quantity');
                }
            });
        }

        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'production_task_id')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->dropColumn('production_task_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_items') && ! Schema::hasColumn('order_items', 'production_task_id')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->unsignedBigInteger('production_task_id')->nullable();
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table): void {
                if (Schema::hasColumn('order_items', 'rejection_reason')) {
                    $table->dropColumn('rejection_reason');
                }

                if (Schema::hasColumn('order_items', 'rejected_quantity')) {
                    $table->dropColumn('rejected_quantity');
                }
            });
        }
    }
};
