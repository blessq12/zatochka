<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sharpening_tools')) {
            Schema::create('sharpening_tools', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
                $table->string('tool_type')->comment('Тип инструмента (ножницы, кусачки, ножи и т.д.)');
                $table->integer('quantity')->default(1)->comment('Количество инструментов');
                $table->text('description')->nullable()->comment('Дополнительное описание');
                $table->timestamps();
                $table->timestamp('deleted_at')->nullable();

                $table->index('order_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sharpening_tools');
    }
};
