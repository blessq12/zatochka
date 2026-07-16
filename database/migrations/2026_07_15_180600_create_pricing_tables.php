<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estimate / ItemPrice / Discount path removed — WorkPrice is the only pricing write model.
 * Kept as a no-op so historical migrate order stays valid; drop migration removes leftovers.
 */
return new class extends Migration
{
    public function up(): void
    {
        // intentionally empty
    }

    public function down(): void
    {
        // intentionally empty
    }
};
