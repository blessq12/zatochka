<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Переименуем колонку, создаём json, переносим данные, дропаем старую (индекс уходит с колонкой)
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            Schema::table('equipment', function (Blueprint $table) {
                $table->renameColumn('serial_number', 'serial_number_old');
            });
            Schema::table('equipment', function (Blueprint $table) {
                $table->json('serial_number')->nullable()->after('serial_number_old')->comment('Части оборудования: [{name, serial_number}]');
            });
            DB::table('equipment')->orderBy('id')->chunk(100, function ($rows) {
                foreach ($rows as $row) {
                    $payload = [];
                    if (!empty($row->serial_number_old)) {
                        $payload[] = [
                            'name' => '',
                            'serial_number' => $row->serial_number_old,
                        ];
                    }
                    DB::table('equipment')->where('id', $row->id)->update([
                        'serial_number' => json_encode($payload),
                    ]);
                }
            });
            Schema::table('equipment', function (Blueprint $table) {
                $table->dropColumn('serial_number_old');
            });
        } else {
            Schema::table('equipment', function (Blueprint $table) {
                $table->json('serial_number')->nullable()->change();
            });
        }

    }

    public function down(): void
    {

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            Schema::table('equipment', function (Blueprint $table) {
                $table->string('serial_number_old')->nullable()->after('serial_number');
            });
            DB::table('equipment')->orderBy('id')->chunk(100, function ($rows) {
                foreach ($rows as $row) {
                    $arr = json_decode($row->serial_number, true);
                    $first = is_array($arr) && count($arr) > 0 ? ($arr[0]['serial_number'] ?? '') : '';
                    DB::table('equipment')->where('id', $row->id)->update(['serial_number_old' => $first]);
                }
            });
            Schema::table('equipment', function (Blueprint $table) {
                $table->dropColumn('serial_number');
            });
            Schema::table('equipment', function (Blueprint $table) {
                $table->renameColumn('serial_number_old', 'serial_number');
            });
        }

        Schema::table('equipment', function (Blueprint $table) {
            $table->index('serial_number');
        });
    }
};
