<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_contacts')) {
            return;
        }

        if (Schema::hasColumn('site_contacts', 'entrance_directions')
            && ! Schema::hasColumn('site_contacts', 'address_details')) {
            return;
        }

        if (! Schema::hasColumn('site_contacts', 'entrance_directions')) {
            Schema::table('site_contacts', function (Blueprint $table) {
                $table->text('entrance_directions')->nullable()->after('address_main');
            });
        }

        if (Schema::hasColumn('site_contacts', 'address_details')) {
            $rows = DB::table('site_contacts')->get(['id', 'address_details']);

            foreach ($rows as $row) {
                $details = json_decode((string) $row->address_details, true);
                $text = is_array($details)
                    ? implode("\n", array_map(static fn ($line): string => (string) $line, $details))
                    : (string) $row->address_details;

                DB::table('site_contacts')
                    ->where('id', $row->id)
                    ->update(['entrance_directions' => trim($text)]);
            }

            Schema::table('site_contacts', function (Blueprint $table) {
                $table->dropColumn('address_details');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('site_contacts')
            || ! Schema::hasColumn('site_contacts', 'entrance_directions')) {
            return;
        }

        if (! Schema::hasColumn('site_contacts', 'address_details')) {
            Schema::table('site_contacts', function (Blueprint $table) {
                $table->json('address_details')->nullable()->after('address_main');
            });
        }

        $rows = DB::table('site_contacts')->get(['id', 'entrance_directions']);

        foreach ($rows as $row) {
            $lines = preg_split("/\r\n|\n|\r/", (string) $row->entrance_directions) ?: [];
            $lines = array_values(array_filter(
                array_map('trim', $lines),
                static fn (string $line): bool => $line !== '',
            ));

            DB::table('site_contacts')
                ->where('id', $row->id)
                ->update(['address_details' => json_encode($lines, JSON_UNESCAPED_UNICODE)]);
        }

        Schema::table('site_contacts', function (Blueprint $table) {
            $table->dropColumn('entrance_directions');
        });
    }
};
