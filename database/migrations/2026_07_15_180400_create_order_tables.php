<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('client_id');
            $table->string('status');
            $table->string('service_type');
            $table->string('billing_type');
            $table->string('urgency');
            $table->boolean('delivery_required')->default(false);
            $table->text('defects')->nullable();
            $table->text('internal_notes')->nullable();
            $table->unsignedBigInteger('warranty_source_order_id')->nullable();
            $table->decimal('estimated_amount', 12, 2);
            $table->string('estimated_currency', 3)->default('RUB');
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
            $table->index('client_id');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('client_equipment_id')->nullable();
            $table->string('tool_name')->nullable();
            $table->string('tool_type')->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->string('status');
            $table->unsignedBigInteger('production_task_id')->nullable();
            $table->unsignedBigInteger('item_price_id')->nullable();
            $table->unsignedBigInteger('warranty_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });

        Schema::create('reception_data', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('order_item_id')->unique();
            $table->string('condition_description');
            $table->text('visual_notes')->nullable();
            $table->json('attachment_refs')->nullable();
            $table->timestamp('received_at');
            $table->foreign('order_item_id')->references('id')->on('order_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reception_data');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
