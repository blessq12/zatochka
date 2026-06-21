<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->text('rework_feedback')->nullable()->after('internal_notes');
            $table->timestamp('rework_returned_at')->nullable()->after('rework_feedback');
            $table->foreignId('rework_returned_by')->nullable()->after('rework_returned_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('rework_returned_by');
            $table->dropColumn(['rework_feedback', 'rework_returned_at']);
        });
    }
};
