<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Поля для доставки
            $table->boolean('needs_delivery')->default(false)->after('problem_description');
            $table->text('delivery_address')->nullable()->after('needs_delivery');

            // Обновляем enum service_type для поддержки sharpening
            $table->enum('service_type', ['sharpening', 'repair', 'maintenance', 'consultation', 'other'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['needs_delivery', 'delivery_address']);
            $table->enum('service_type', ['repair', 'maintenance', 'consultation', 'other'])->change();
        });
    }
};
