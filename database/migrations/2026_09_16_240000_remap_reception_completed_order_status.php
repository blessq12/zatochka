<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->where('status', 'reception_completed')
            ->update(['status' => 'master_assigned']);
    }

    public function down(): void
    {
        // Irreversible: cannot distinguish remapped rows from native master_assigned.
    }
};
