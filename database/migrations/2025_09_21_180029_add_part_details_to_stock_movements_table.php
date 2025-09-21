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
        Schema::table('stock_movements', function (Blueprint $table) {
            // Добавляем поля для "заморозки" данных запчасти
            $table->string('part_name')->nullable()->after('stock_item_id'); // Название запчасти на момент движения
            $table->string('part_sku')->nullable()->after('part_name'); // Артикул запчасти на момент движения
            $table->decimal('part_purchase_price', 10, 2)->nullable()->after('part_sku'); // Закупочная цена на момент движения
            $table->decimal('part_retail_price', 10, 2)->nullable()->after('part_purchase_price'); // Розничная цена на момент движения
            $table->string('part_unit', 20)->nullable()->after('part_retail_price'); // Единица измерения на момент движения
            $table->string('part_supplier')->nullable()->after('part_unit'); // Поставщик на момент движения
            $table->string('part_manufacturer')->nullable()->after('part_supplier'); // Производитель на момент движения
            $table->string('part_model')->nullable()->after('part_manufacturer'); // Модель на момент движения
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn([
                'part_name',
                'part_sku',
                'part_purchase_price',
                'part_retail_price',
                'part_unit',
                'part_supplier',
                'part_manufacturer',
                'part_model',
            ]);
        });
    }
};
