<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('inn')->nullable();
            $table->string('ogrn')->nullable();
            $table->string('legal_address')->nullable();
            $table->string('actual_address')->nullable();
            $table->timestamps();
        });

        Schema::create('site_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('address_main')->nullable();
            $table->text('address_directions')->nullable();
            $table->string('social_email')->nullable();
            $table->timestamps();
        });

        Schema::create('site_social_links', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('site_schedule_days', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('hours');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('site_faq_items', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->json('answer_lines');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('site_delivery_conditions', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('site_delivery_advantages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('site_price_items', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('price');
            $table->string('prefix')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('site_legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('type');
            $table->string('title');
            $table->longText('body_html');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_legal_documents');
        Schema::dropIfExists('site_price_items');
        Schema::dropIfExists('site_delivery_advantages');
        Schema::dropIfExists('site_delivery_conditions');
        Schema::dropIfExists('site_faq_items');
        Schema::dropIfExists('site_schedule_days');
        Schema::dropIfExists('site_social_links');
        Schema::dropIfExists('site_contacts');
        Schema::dropIfExists('site_companies');
    }
};
