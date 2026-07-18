<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clients') && ! Schema::hasColumn('clients', 'password')) {
            Schema::table('clients', function (Blueprint $table): void {
                $table->string('password')->nullable()->after('delivery_address');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            $clientUserIds = DB::table('users')
                ->where('role', 'client')
                ->pluck('id');

            if ($clientUserIds->isNotEmpty()) {
                DB::table('personal_access_tokens')
                    ->where('tokenable_type', 'App\\Models\\User')
                    ->whereIn('tokenable_id', $clientUserIds)
                    ->delete();

                DB::table('users')->where('role', 'client')->delete();
            }
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'client_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropUnique(['client_id']);
            });

            Schema::table('users', function (Blueprint $table): void {
                $table->dropForeign(['client_id']);
            });

            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('client_id');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'requires_password_set')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('requires_password_set');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'client_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->unsignedBigInteger('client_id')->nullable()->unique()->after('role');
                $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'requires_password_set')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->boolean('requires_password_set')->default(false)->after('password');
            });
        }

        if (Schema::hasTable('clients') && Schema::hasColumn('clients', 'password')) {
            Schema::table('clients', function (Blueprint $table): void {
                $table->dropColumn('password');
            });
        }
    }
};
