<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'users',
            function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('username')->unique();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->boolean('approved')->default(0);
                $table->boolean('blocked')->default(0);
                $table->timestamps();
            }
        );

        /*
         * Create the initial admin user
         */
        DB::table('users')->insert([
            'id' => Str::uuid(),
            'username' => "admin",
            'email' => "admin-user@example.org",
            'email_verified_at' => now(),
            'password' => Hash::make('admin'),
            'approved' => 1,
            'blocked' => 0,
            'created_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
