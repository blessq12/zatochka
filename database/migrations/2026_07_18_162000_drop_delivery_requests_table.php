<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('delivery_requests');
    }

    public function down(): void
    {
        // Intentionally empty: Delivery BC removed; recreate only via historical migration if needed.
    }
};
