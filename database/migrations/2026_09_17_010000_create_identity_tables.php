<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identities', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('identity_actor_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('identity_id')->unique()->constrained('identities')->cascadeOnDelete();
            $table->string('actor_type');
            $table->unsignedBigInteger('actor_id');
            $table->timestamps();

            $table->unique(['actor_type', 'actor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identity_actor_links');
        Schema::dropIfExists('identities');
    }
};
