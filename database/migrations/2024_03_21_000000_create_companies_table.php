<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            // Основная инфа
            $table->string('name');
            $table->string('legal_name')->nullable(); // Юр. название
            $table->string('inn', 12)->nullable(); // ИНН
            $table->string('kpp', 9)->nullable(); // КПП
            $table->string('ogrn', 15)->nullable(); // ОГРН

            // Юридическая инфа
            $table->string('legal_address')->nullable(); // Юр. адрес
            $table->string('website')->nullable();

            // Банковская инфа
            $table->string('bank_name')->nullable();
            $table->string('bank_bik', 9)->nullable();
            $table->string('bank_account', 20)->nullable(); // Расчетный счет
            $table->string('bank_cor_account', 20)->nullable(); // Кор. счет

            // Дополнительная инфа
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->json('additional_data')->nullable(); // Для доп. полей

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
