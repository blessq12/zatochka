<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Удаляем foreign key для branch_id перед удалением колонки
            if (Schema::hasColumn('users', 'branch_id')) {
                try {
                    $table->dropForeign(['branch_id']);
                } catch (\Exception $e) {
                    // Игнорируем, если foreign key уже удален
                }
            }
        });
        
        // Удаляем колонки в отдельном вызове, так как dropForeign должен быть отдельно
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];
            
            if (Schema::hasColumn('users', 'role')) {
                $columnsToDrop[] = 'role';
            }
            if (Schema::hasColumn('users', 'two_factor_secret')) {
                $columnsToDrop[] = 'two_factor_secret';
            }
            if (Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $columnsToDrop[] = 'two_factor_recovery_codes';
            }
            if (Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $columnsToDrop[] = 'two_factor_confirmed_at';
            }
            if (Schema::hasColumn('users', 'branch_id')) {
                $columnsToDrop[] = 'branch_id';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Восстанавливаем колонки в обратном порядке
            $table->json('role')->nullable()->after('email');
            $table->text('two_factor_secret')->nullable()->after('password');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null')->after('two_factor_confirmed_at');
        });
    }
};
