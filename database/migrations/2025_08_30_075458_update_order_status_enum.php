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
        // Сначала изменяем enum на VARCHAR, чтобы избежать проблем с данными
        DB::statement("ALTER TABLE orders MODIFY COLUMN status VARCHAR(50)");

        // Обновляем существующие данные
        DB::table('orders')->where('status', 'ready')->update(['status' => 'ready_for_pickup']);
        DB::table('orders')->where('status', 'completed')->update(['status' => 'delivered']);

        // Теперь изменяем обратно на enum с новыми значениями
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('new', 'confirmed', 'courier_pickup', 'master_received', 'in_progress', 'work_completed', 'courier_delivery', 'ready_for_pickup', 'delivered', 'payment_received', 'closed', 'cancelled')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Изменяем на VARCHAR
        DB::statement("ALTER TABLE orders MODIFY COLUMN status VARCHAR(50)");

        // Обновляем данные обратно
        DB::table('orders')->where('status', 'ready_for_pickup')->update(['status' => 'ready']);
        DB::table('orders')->where('status', 'delivered')->update(['status' => 'completed']);

        // Возвращаем старый enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('new', 'in_progress', 'completed', 'cancelled')");
    }
};
