<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->string('number')->nullable()->after('id');
        });

        $payments = DB::table('payments')->orderBy('id')->get(['id', 'accepted_at']);

        foreach ($payments as $payment) {
            $year = $payment->accepted_at !== null
                ? date('y', strtotime((string) $payment->accepted_at))
                : date('y');

            DB::table('payments')
                ->where('id', $payment->id)
                ->update([
                    'number' => sprintf('PMT-%s-%d', $year, $payment->id),
                ]);
        }

        Schema::table('payments', function (Blueprint $table): void {
            $table->unique('number');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropUnique(['number']);
            $table->dropColumn('number');
        });
    }
};
