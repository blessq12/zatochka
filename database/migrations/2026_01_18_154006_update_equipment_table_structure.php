<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            // Добавляем deleted_at для SoftDeletes
            if (!Schema::hasColumn('equipment', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
            }

            // Добавляем manufacturer если его нет (или используем brand)
            if (!Schema::hasColumn('equipment', 'manufacturer')) {
                $table->string('manufacturer')->nullable()->after('brand')->comment('Производитель (алиас для brand)');
            }

            // Добавляем type если его нет
            if (!Schema::hasColumn('equipment', 'type')) {
                $table->string('type')->nullable()->after('name')->comment('Тип оборудования');
            }

            // Добавляем description если его нет
            if (!Schema::hasColumn('equipment', 'description')) {
                $table->text('description')->nullable()->after('model')->comment('Описание');
            }

            // Делаем serial_number nullable если он required
            $table->string('serial_number')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            if (Schema::hasColumn('equipment', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
            if (Schema::hasColumn('equipment', 'manufacturer')) {
                $table->dropColumn('manufacturer');
            }
            if (Schema::hasColumn('equipment', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('equipment', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
