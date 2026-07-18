<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table): void {
            $table->decimal('unit_price', 14, 2)->default(0)->after('category');
            $table->string('currency', 3)->default('RUB')->after('unit_price');
        });

        Schema::table('warehouse_movements', function (Blueprint $table): void {
            $table->decimal('unit_price', 14, 2)->nullable()->after('quantity');
            $table->string('currency', 3)->nullable()->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table): void {
            $table->dropColumn(['unit_price', 'currency']);
        });

        Schema::table('warehouse_movements', function (Blueprint $table): void {
            $table->dropColumn(['unit_price', 'currency']);
        });
    }
};
