<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_additionals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_additional_id')->unique()->constrained('profile_additionals');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('managers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_additional_id')->unique()->constrained('profile_additionals');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('masters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_additional_id')->unique()->constrained('profile_additionals');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masters');
        Schema::dropIfExists('managers');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('profile_additionals');
    }
};
