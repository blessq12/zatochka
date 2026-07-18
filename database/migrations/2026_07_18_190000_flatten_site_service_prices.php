<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_service_prices')) {
            Schema::create('site_service_prices', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();
                $table->string('category');
                $table->string('name');
                $table->string('price');
                $table->string('prefix')->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index('category');
            });
        }

        if (Schema::hasTable('site_price_items') && Schema::hasTable('site_price_blocks')) {
            $existing = (int) DB::table('site_service_prices')->count();

            if ($existing === 0) {
                $now = now();
                $nextId = 1;
                $blocks = DB::table('site_price_blocks')->orderBy('sort_order')->get()->keyBy('id');
                $items = DB::table('site_price_items')->orderBy('sort_order')->get();

                foreach ($items as $item) {
                    $block = $blocks->get($item->price_block_id);
                    if ($block === null) {
                        continue;
                    }

                    DB::table('site_service_prices')->insert([
                        'id' => $nextId++,
                        'category' => (string) $block->type,
                        'name' => (string) $item->name,
                        'price' => (string) $item->price,
                        'prefix' => $item->prefix,
                        'description' => $item->description,
                        'sort_order' => (int) $item->sort_order,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                DB::table('entity_id_sequences')->updateOrInsert(
                    ['name' => 'site_service_price'],
                    ['next_value' => $nextId],
                );
            }

            Schema::dropIfExists('site_price_items');
            Schema::dropIfExists('site_price_blocks');
        }
    }

    public function down(): void
    {
        // Irreversible flatten — keep site_service_prices.
    }
};
