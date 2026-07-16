<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        if (Schema::hasTable('production_tasks') && ! Schema::hasColumn('production_tasks', 'order_id')) {
            Schema::table('production_tasks', function (Blueprint $table): void {
                $table->string('order_id', 32)->nullable()->after('id');
            });

            $tasks = DB::table('production_tasks')
                ->join('order_items', 'order_items.id', '=', 'production_tasks.order_item_id')
                ->select('production_tasks.id', 'order_items.order_id')
                ->get();

            foreach ($tasks as $task) {
                DB::table('production_tasks')
                    ->where('id', $task->id)
                    ->update(['order_id' => $task->order_id]);
            }

            $orderIds = DB::table('production_tasks')
                ->whereNotNull('order_id')
                ->distinct()
                ->pluck('order_id');

            foreach ($orderIds as $orderId) {
                $taskIds = DB::table('production_tasks')
                    ->where('order_id', $orderId)
                    ->orderBy('id')
                    ->pluck('id')
                    ->all();

                if (count($taskIds) <= 1) {
                    continue;
                }

                $keeperId = (int) $taskIds[0];
                $duplicateIds = array_slice($taskIds, 1);

                foreach ($duplicateIds as $duplicateId) {
                    $duplicateId = (int) $duplicateId;

                    foreach (['diagnoses', 'work_executions'] as $tableName) {
                        $hasKeeper = DB::table($tableName)
                            ->where('production_task_id', $keeperId)
                            ->exists();

                        if ($hasKeeper) {
                            DB::table($tableName)
                                ->where('production_task_id', $duplicateId)
                                ->delete();
                        } else {
                            DB::table($tableName)
                                ->where('production_task_id', $duplicateId)
                                ->update(['production_task_id' => $keeperId]);
                        }
                    }

                    DB::table('master_comments')
                        ->where('production_task_id', $duplicateId)
                        ->update(['production_task_id' => $keeperId]);

                    DB::table('production_tasks')->where('id', $duplicateId)->delete();
                }
            }

            // Drop orphan rows without order_id before enforcing NOT NULL.
            DB::table('production_tasks')->whereNull('order_id')->delete();

            Schema::table('production_tasks', function (Blueprint $table): void {
                $table->dropUnique(['order_item_id']);
                $table->dropColumn('order_item_id');
            });

            Schema::table('production_tasks', function (Blueprint $table): void {
                $table->unique('order_id');
                $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'production_task_id')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->dropColumn('production_task_id');
            });
        }

        if (Schema::hasTable('master_comments') && ! Schema::hasColumn('master_comments', 'order_item_id')) {
            Schema::table('master_comments', function (Blueprint $table): void {
                $table->unsignedBigInteger('order_item_id')->nullable()->after('production_task_id');
                $table->foreign('order_item_id')->references('id')->on('order_items')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('master_comments') && Schema::hasColumn('master_comments', 'order_item_id')) {
            Schema::table('master_comments', function (Blueprint $table): void {
                $table->dropForeign(['order_item_id']);
                $table->dropColumn('order_item_id');
            });
        }

        if (Schema::hasTable('order_items') && ! Schema::hasColumn('order_items', 'production_task_id')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->unsignedBigInteger('production_task_id')->nullable();
            });
        }

        if (Schema::hasTable('production_tasks') && Schema::hasColumn('production_tasks', 'order_id')) {
            Schema::table('production_tasks', function (Blueprint $table): void {
                $table->dropForeign(['order_id']);
                $table->dropUnique(['order_id']);
                $table->unsignedBigInteger('order_item_id')->nullable()->after('id');
            });

            $firstItems = DB::table('order_items')
                ->select('order_id', DB::raw('MIN(id) as item_id'))
                ->groupBy('order_id')
                ->pluck('item_id', 'order_id');

            foreach ($firstItems as $orderId => $itemId) {
                DB::table('production_tasks')
                    ->where('order_id', $orderId)
                    ->update(['order_item_id' => $itemId]);
            }

            Schema::table('production_tasks', function (Blueprint $table): void {
                $table->dropColumn('order_id');
            });

            Schema::table('production_tasks', function (Blueprint $table): void {
                $table->unique('order_item_id');
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
