<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('warehouse_categories')) {
            Schema::create('warehouse_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->comment('Название категории');
                $table->string('slug')->unique()->comment('URL-ключ');
                $table->text('description')->nullable()->comment('Описание категории');
                $table->integer('sort_order')->default(0)->comment('Порядок сортировки');
                $table->boolean('is_active')->default(true)->comment('Активна ли категория');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_categories');
    }
};
