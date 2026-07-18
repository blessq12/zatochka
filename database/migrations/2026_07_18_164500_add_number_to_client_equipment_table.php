<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_equipment', function (Blueprint $table): void {
            $table->string('number')->nullable()->after('id');
        });

        $rows = DB::table('client_equipment')->orderBy('id')->get(['id']);

        foreach ($rows as $row) {
            DB::table('client_equipment')
                ->where('id', $row->id)
                ->update(['number' => sprintf('EQP-%d', $row->id)]);
        }

        Schema::table('client_equipment', function (Blueprint $table): void {
            $table->unique('number');
        });
    }

    public function down(): void
    {
        Schema::table('client_equipment', function (Blueprint $table): void {
            $table->dropUnique(['number']);
            $table->dropColumn('number');
        });
    }
};
