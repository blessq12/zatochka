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
        // Сначала удаляем foreign key constraint из таблицы tools
        Schema::table('tools', function (Blueprint $table) {
            $table->dropForeign(['tool_type_id']);
            $table->dropColumn('tool_type_id');
        });

        // Удаляем таблицы
        Schema::dropIfExists('order_tools');
        Schema::dropIfExists('tool_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Восстанавливаем таблицу tool_types
        Schema::create('tool_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        // Восстанавливаем таблицу order_tools
        Schema::create('order_tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('tool_id')->constrained()->onDelete('cascade');
            $table->text('problem_description')->nullable();
            $table->text('work_description')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'tool_id']);
        });

        // Восстанавливаем поле tool_type_id в таблице tools
        Schema::table('tools', function (Blueprint $table) {
            $table->foreignId('tool_type_id')->constrained()->onDelete('cascade');
        });
    }
};
