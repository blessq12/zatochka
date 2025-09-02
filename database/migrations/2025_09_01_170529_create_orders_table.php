<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('master_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('order_number')->unique();
            $table->foreignId('service_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('status_id')->constrained('order_statuses')->onDelete('cascade');
            $table->enum('urgency', ['normal', 'urgent'])->default('normal');
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('discount_id')->nullable()->constrained('discount_rules')->onDelete('set null');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('final_price', 10, 2);
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('profit', 10, 2)->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
