<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('legal_name');
                $table->string('inn', 12)->unique();
                $table->string('kpp', 9)->nullable();
                $table->string('ogrn', 15)->nullable();
                $table->text('legal_address');
                $table->text('description')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('bank_bik', 9)->nullable();
                $table->string('bank_account', 20)->nullable();
                $table->string('bank_cor_account', 20)->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_deleted')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
