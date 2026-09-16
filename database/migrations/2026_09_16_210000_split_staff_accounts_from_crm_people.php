<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manager_accounts', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('id')->references('id')->on('managers')->cascadeOnDelete();
        });

        Schema::create('master_accounts', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('id')->references('id')->on('masters')->cascadeOnDelete();
        });

        foreach (DB::table('managers')->get() as $row) {
            DB::table('manager_accounts')->insert([
                'id' => $row->id,
                'email' => $row->email,
                'password' => $row->password,
                'remember_token' => $row->remember_token ?? null,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        foreach (DB::table('masters')->get() as $row) {
            DB::table('master_accounts')->insert([
                'id' => $row->id,
                'email' => $row->email,
                'password' => $row->password,
                'remember_token' => $row->remember_token ?? null,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\\Infrastructure\\Identity\\Model\\ManagerModel')
            ->update(['tokenable_type' => 'App\\Infrastructure\\Identity\\Model\\ManagerAccountModel']);

        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\\Infrastructure\\Identity\\Model\\MasterModel')
            ->update(['tokenable_type' => 'App\\Infrastructure\\Identity\\Model\\MasterAccountModel']);

        Schema::table('managers', function (Blueprint $table): void {
            $table->dropColumn(['password', 'remember_token']);
        });

        Schema::table('masters', function (Blueprint $table): void {
            $table->dropColumn(['password', 'remember_token']);
        });
    }

    public function down(): void
    {
        Schema::table('managers', function (Blueprint $table): void {
            $table->string('password')->nullable()->after('email');
            $table->rememberToken();
        });

        Schema::table('masters', function (Blueprint $table): void {
            $table->string('password')->nullable()->after('email');
            $table->rememberToken();
        });

        foreach (DB::table('manager_accounts')->get() as $account) {
            DB::table('managers')->where('id', $account->id)->update([
                'password' => $account->password,
                'remember_token' => $account->remember_token,
            ]);
        }

        foreach (DB::table('master_accounts')->get() as $account) {
            DB::table('masters')->where('id', $account->id)->update([
                'password' => $account->password,
                'remember_token' => $account->remember_token,
            ]);
        }

        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\\Infrastructure\\Identity\\Model\\ManagerAccountModel')
            ->update(['tokenable_type' => 'App\\Infrastructure\\Identity\\Model\\ManagerModel']);

        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\\Infrastructure\\Identity\\Model\\MasterAccountModel')
            ->update(['tokenable_type' => 'App\\Infrastructure\\Identity\\Model\\MasterModel']);

        Schema::dropIfExists('master_accounts');
        Schema::dropIfExists('manager_accounts');
    }
};
