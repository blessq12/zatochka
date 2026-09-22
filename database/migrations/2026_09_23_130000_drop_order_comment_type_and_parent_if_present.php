<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cleanup for DBs that already ran the short-lived type/parent columns.
 * Fresh installs never create those columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_comments')) {
            return;
        }

        $hasParent = Schema::hasColumn('order_comments', 'parent_id');
        $hasType = Schema::hasColumn('order_comments', 'type');
        if (! $hasParent && ! $hasType) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::disableForeignKeyConstraints();

            Schema::create('order_comments_tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->string('author_type');
                $table->unsignedBigInteger('author_id');
                $table->text('body');
                $table->timestamps();
                $table->index('order_id');
                $table->index(['author_type', 'author_id']);
            });

            DB::statement(
                'INSERT INTO order_comments_tmp (id, order_id, author_type, author_id, body, created_at, updated_at)
                 SELECT id, order_id, author_type, author_id, body, created_at, updated_at FROM order_comments'
            );

            Schema::drop('order_comments');
            Schema::rename('order_comments_tmp', 'order_comments');

            Schema::enableForeignKeyConstraints();

            return;
        }

        Schema::table('order_comments', function (Blueprint $table) use ($hasParent, $hasType): void {
            if ($hasParent) {
                $table->dropConstrainedForeignId('parent_id');
            }
            if ($hasType) {
                $table->dropColumn('type');
            }
        });
    }

    public function down(): void
    {
        // no-op: columns were a temporary experiment
    }
};
