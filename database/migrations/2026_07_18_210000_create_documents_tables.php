<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table): void {
            $table->string('type', 64)->primary();
            $table->string('title');
            $table->longText('body_html');
            $table->timestamp('updated_at');
        });

        Schema::create('document_templates', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->string('kind', 64)->unique();
            $table->string('name');
            $table->longText('body_html');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('legal_documents');
    }
};
