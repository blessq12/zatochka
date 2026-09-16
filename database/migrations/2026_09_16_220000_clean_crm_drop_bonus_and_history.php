<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('client_history');
        Schema::dropIfExists('client_leads');

        Schema::table('clients', function (Blueprint $table): void {
            if (Schema::hasColumn('clients', 'bonus_account_id')) {
                $table->dropColumn('bonus_account_id');
            }

            if (Schema::hasColumn('clients', 'bonus_balance')) {
                $table->dropColumn('bonus_balance');
            }
        });

        DB::table('entity_id_sequences')
            ->whereIn('name', ['bonus_account', 'client_lead', 'client_history'])
            ->delete();
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            if (! Schema::hasColumn('clients', 'bonus_account_id')) {
                $table->unsignedBigInteger('bonus_account_id')->default(0)->after('email');
            }

            if (! Schema::hasColumn('clients', 'bonus_balance')) {
                $table->decimal('bonus_balance', 12, 2)->default(0)->after('bonus_account_id');
            }
        });

        if (! Schema::hasTable('client_history')) {
            Schema::create('client_history', function (Blueprint $table): void {
                $table->unsignedBigInteger('id')->primary();
                $table->unsignedBigInteger('client_id');
                $table->string('order_id', 32);
                $table->string('note');
                $table->timestamp('recorded_at');
                $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('client_leads')) {
            Schema::create('client_leads', function (Blueprint $table): void {
                $table->unsignedBigInteger('id')->primary();
                $table->unsignedBigInteger('client_id');
                $table->json('service_types');
                $table->text('comment')->nullable();
                $table->json('intake_data')->nullable();
                $table->boolean('needs_delivery')->default(false);
                $table->string('delivery_address')->nullable();
                $table->string('status')->default('new');
                $table->timestamps();
                $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
                $table->index('status');
            });
        }
    }
};
