<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('discount_id');
        });

        // Миграция данных: price = actual_price ?? estimated_price ?? 0
        \DB::statement('UPDATE orders SET price = COALESCE(actual_price, estimated_price, 0)');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'estimated_price')) {
                $table->dropColumn('estimated_price');
            }
            if (Schema::hasColumn('orders', 'actual_price')) {
                $table->dropColumn('actual_price');
            }
            if (Schema::hasColumn('orders', 'delivery_cost')) {
                $table->dropColumn('delivery_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('estimated_price', 10, 2)->nullable()->after('discount_id');
            $table->decimal('actual_price', 10, 2)->nullable()->after('estimated_price');
            $table->decimal('delivery_cost', 10, 2)->nullable()->after('delivery_address');
        });

        \DB::table('orders')->update([
            'estimated_price' => \DB::raw('price'),
            'actual_price' => \DB::raw('price'),
        ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
