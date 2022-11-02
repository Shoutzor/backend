<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoutzorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'shoutzor',
            function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('value');
            }
        );

        DB::table('shoutzor')->insert([
            [
                'key' => 'version',
                'value' => '1.0'
            ],
            [
                'key' => 'user_manual_approve_required',
                'value' => 'false'
            ],
            [
                'key' => 'user_must_verify_email',
                'value' => 'false'
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
        Schema::dropIfExists('shoutzor');
    }
}
