<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_company_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('owner_name');
            $table->string('inn');
            $table->string('ogrn');
            $table->text('legal_address');
            $table->text('actual_address');
            $table->timestamps();
        });

        Schema::create('site_contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('contact_person');
            $table->string('phone');
            $table->string('phone_tel');
            $table->string('email');
            $table->string('address_main');
            $table->json('address_details');
            $table->json('social_links');
            $table->timestamps();
        });

        Schema::create('site_delivery_infos', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->json('free_conditions');
            $table->json('advantages');
            $table->timestamps();
        });

        Schema::create('site_schedule_days', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->boolean('is_day_off')->default(false);
            $table->string('day_off_text')->nullable();
            $table->string('workshop')->nullable();
            $table->string('delivery')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('site_price_blocks', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('type');
            $table->string('title');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('site_price_items', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('price_block_id');
            $table->string('name');
            $table->string('price');
            $table->string('prefix')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('price_block_id')
                ->references('id')
                ->on('site_price_blocks')
                ->cascadeOnDelete();
            $table->index('price_block_id');
        });

        Schema::create('site_faq_items', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('question');
            $table->json('answer_lines');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_price_items');
        Schema::dropIfExists('site_price_blocks');
        Schema::dropIfExists('site_faq_items');
        Schema::dropIfExists('site_schedule_days');
        Schema::dropIfExists('site_delivery_infos');
        Schema::dropIfExists('site_contacts');
        Schema::dropIfExists('site_company_profiles');
    }
};
