<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'settings',
            function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('value');
                $table->string('name');
                $table->string('description');
                $table->boolean('readonly')->default(false);
            }
        );
 
        DB::table('settings')->insert([
            [
                'key' => 'version',
                'value' => '1.0',
                'name' => 'Name',
                'description' => 'The current version of shoutzor',
                'readonly' => true
            ],
            [
                'key' => 'user_manual_approve_required',
                'value' => 'false',
                'name' => 'Require manual approval',
                'description' => 'When enabled, newly registered accounts will require manual approval',
                'readonly' => false
            ],
            [
                'key' => 'user_must_verify_email',
                'value' => 'false',
                'name' => 'Require email verification',
                'description' => 'When enabled, users who create a new account will be required to confirm their email',
                'readonly' => false
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
