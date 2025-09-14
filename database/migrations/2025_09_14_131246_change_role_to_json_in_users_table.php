<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Сначала извлекаем данные
        $users = DB::table('users')->whereNotNull('role')->get();

        // Изменяем тип поля на TEXT
        Schema::table('users', function (Blueprint $table) {
            $table->text('role')->nullable()->change();
        });

        // Конвертируем данные в JSON строку
        foreach ($users as $user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['role' => json_encode([$user->role])]);
        }

        // Изменяем тип поля на JSON
        Schema::table('users', function (Blueprint $table) {
            $table->json('role')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Конвертируем JSON обратно в строку (берем первую роль)
        $users = DB::table('users')->whereNotNull('role')->get();
        foreach ($users as $user) {
            $roles = json_decode($user->role, true);
            $firstRole = is_array($roles) && count($roles) > 0 ? $roles[0] : 'manager';
            DB::table('users')
                ->where('id', $user->id)
                ->update(['role' => $firstRole]);
        }

        // Изменяем тип поля на TEXT
        Schema::table('users', function (Blueprint $table) {
            $table->text('role')->nullable()->change();
        });

        // Возвращаем enum
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['manager', 'master'])->default('manager')->change();
        });
    }
};
