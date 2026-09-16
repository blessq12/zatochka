<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('managers', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('masters', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('client_accounts', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('client_id')->unique();
            $table->string('phone')->unique();
            $table->string('password');
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
        });

        if (Schema::hasTable('users')) {
            $managers = DB::table('users')->where('role', 'manager')->get();
            foreach ($managers as $row) {
                DB::table('managers')->insert([
                    'id' => $row->id,
                    'name' => $row->name,
                    'email' => $row->email,
                    'password' => $row->password,
                    'remember_token' => $row->remember_token ?? null,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);

                DB::table('personal_access_tokens')
                    ->where('tokenable_type', 'App\\Models\\User')
                    ->where('tokenable_id', $row->id)
                    ->update(['tokenable_type' => 'App\\Infrastructure\\Identity\\Model\\ManagerModel']);
            }

            $masters = DB::table('users')->where('role', 'master')->get();
            foreach ($masters as $row) {
                DB::table('masters')->insert([
                    'id' => $row->id,
                    'name' => $row->name,
                    'email' => $row->email,
                    'password' => $row->password,
                    'remember_token' => $row->remember_token ?? null,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);

                DB::table('personal_access_tokens')
                    ->where('tokenable_type', 'App\\Models\\User')
                    ->where('tokenable_id', $row->id)
                    ->update(['tokenable_type' => 'App\\Infrastructure\\Identity\\Model\\MasterModel']);
            }
        }

        if (Schema::hasTable('clients') && Schema::hasColumn('clients', 'password')) {
            $clients = DB::table('clients')->whereNotNull('password')->where('password', '!=', '')->get();
            $accountId = (int) (DB::table('entity_id_sequences')->where('name', 'client_account')->value('next_value') ?? 1);

            foreach ($clients as $client) {
                DB::table('client_accounts')->insert([
                    'id' => $accountId,
                    'client_id' => $client->id,
                    'phone' => $client->phone,
                    'password' => $client->password,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('personal_access_tokens')
                    ->where('tokenable_type', 'App\\Infrastructure\\CRM\\Model\\ClientModel')
                    ->where('tokenable_id', $client->id)
                    ->update([
                        'tokenable_type' => 'App\\Infrastructure\\Identity\\Model\\ClientAccountModel',
                        'tokenable_id' => $accountId,
                    ]);

                $accountId++;
            }

            DB::table('entity_id_sequences')->updateOrInsert(
                ['name' => 'client_account'],
                ['next_value' => $accountId],
            );

            Schema::table('clients', function (Blueprint $table): void {
                $table->dropColumn('password');
            });
        }

        $maxStaffId = max(
            (int) DB::table('managers')->max('id'),
            (int) DB::table('masters')->max('id'),
            (int) (DB::table('entity_id_sequences')->where('name', 'user')->value('next_value') ?? 1) - 1,
        );

        DB::table('entity_id_sequences')->updateOrInsert(
            ['name' => 'staff'],
            ['next_value' => $maxStaffId + 1],
        );

        Schema::dropIfExists('users');
    }

    public function down(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role')->default('manager');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        foreach (DB::table('managers')->get() as $row) {
            DB::table('users')->insert([
                'id' => $row->id,
                'name' => $row->name,
                'email' => $row->email,
                'role' => 'manager',
                'password' => $row->password,
                'remember_token' => $row->remember_token,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        foreach (DB::table('masters')->get() as $row) {
            DB::table('users')->insert([
                'id' => $row->id,
                'name' => $row->name,
                'email' => $row->email,
                'role' => 'master',
                'password' => $row->password,
                'remember_token' => $row->remember_token,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        if (! Schema::hasColumn('clients', 'password')) {
            Schema::table('clients', function (Blueprint $table): void {
                $table->string('password')->nullable()->after('delivery_address');
            });
        }

        foreach (DB::table('client_accounts')->get() as $account) {
            DB::table('clients')->where('id', $account->client_id)->update([
                'password' => $account->password,
            ]);
        }

        Schema::dropIfExists('client_accounts');
        Schema::dropIfExists('masters');
        Schema::dropIfExists('managers');
    }
};
