<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
                $table->json('value'); // the actual data will always be stored in "value->data"
                $table->string('type');
                $table->string('name');
                $table->string('description');
                $table->boolean('readonly')->default(false);
            }
        );
 
        DB::table('settings')->insert([
            [
                'key' => 'version',
                'value' => json_encode(['data' => '1.0']),
                'type' => 'string',
                'name' => 'Name',
                'description' => 'The current version of shoutzor',
                'readonly' => true
            ],
            [
                'key' => 'user_manual_approve_required',
                'value' => json_encode(['data' => false]),
                'type' => 'boolean',
                'name' => 'Require manual approval',
                'description' => 'When enabled, newly registered accounts will require manual approval',
                'readonly' => false
            ],
            [
                'key' => 'user_must_verify_email',
                'value' => json_encode(['data' => false]),
                'type' => 'boolean',
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
