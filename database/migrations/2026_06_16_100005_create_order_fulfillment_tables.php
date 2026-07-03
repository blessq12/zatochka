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
            $table->string('order_number')->unique();
            $table->string('status')->default('new');
            $table->json('service_types');
            $table->string('urgency')->default('standard');
            $table->boolean('is_warranty')->default(false);
            $table->boolean('needs_delivery')->default(false);
            $table->string('delivery_address')->nullable();
            $table->text('problem_description')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('rework_feedback')->nullable();
            $table->timestamp('rework_returned_at')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('source')->default('manual');
            $table->json('client_snapshot')->nullable();
            $table->foreignId('lead_id')->nullable()->constrained('site_leads')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('master_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rework_returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('warranty_parent_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('taken_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('master_id');
        });

        Schema::create('order_tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('tool_type');
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
        });

        Schema::create('order_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('order_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_item_id')->constrained();
            $table->decimal('quantity', 10, 3);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });

        Schema::table('site_leads', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::table('site_leads', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::dropIfExists('order_materials');
        Schema::dropIfExists('order_works');
        Schema::dropIfExists('order_tools');
        Schema::dropIfExists('orders');
    }
};
