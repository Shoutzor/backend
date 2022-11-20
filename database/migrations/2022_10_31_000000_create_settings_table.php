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
                $table->string('type'); // limited by the types that the "settype()" function supports
                $table->string('name');
                $table->string('description');
                $table->string('group')->default('shoutzor'); // only used by the frontend to group settings together
                $table->integer('order')->default(1); // only used by the frontend to determine the order
                // Rules to apply to the data. For array it will be applied to it's children
                // By default there's already a validation for the "type" and a "required" rule
                $table->string('validation');
                $table->boolean('readonly')->default(false);
            }
        );
 
        DB::table('settings')->insert([
            [
                'key' => 'version',
                'value' => $this->toValue('1.0'),
                'type' => 'string',
                'name' => 'Name',
                'description' => 'The current version of shoutzor',
                'group' => 'shoutzor',
                'order' => 10,
                'validation' => '',
                'readonly' => true
            ],
            [
                'key' => 'user_must_verify_email',
                'value' => $this->toValue(false),
                'type' => 'boolean',
                'name' => 'Require email verification',
                'description' => 'When enabled, users who create a new account will be required to confirm their email',
                'group' => 'users',
                'order' => 10,
                'validation' => '',
                'readonly' => false
            ],
            [
                'key' => 'user_manual_approve_required',
                'value' => $this->toValue(false),
                'type' => 'boolean',
                'name' => 'Require manual approval',
                'description' => 'When enabled, newly registered accounts will require manual approval',
                'group' => 'users',
                'order' => 20,
                'validation' => '',
                'readonly' => false
            ],
            [
                'key' => 'upload_max_size',
                'value' => $this->toValue(20),
                'type' => 'integer',
                'name' => 'Max Filesize',
                'description' => 'The maximum allowed filesize for uploads (in MB). Make sure the backend is configured to accept the upload size too.',
                'group' => 'uploads',
                'order' => 10,
                'validation' => '',
                'readonly' => false
            ],
            [
                'key' => 'upload_allowed_extensions',
                'value' => $this->toValue([
                    // Audio extensions
                    'mp3', 'ogg', 'wav', 'flac', 'm4a', 'wma', 'weba',
                    //Video is not implemented yet
                    //'webm', 'avi', 'mp4', 'mkv'
                ]),
                'type' => 'array',
                'name' => 'Allowed Extensions',
                'description' => 'The file extensions that are allowed for uploads. Only letters & numbers are allowed. This will not affect files that have already been processed.',
                'group' => 'uploads',
                'order' => 20,
                'validation' => 'bail|sometimes|required|alpha_num',
                'readonly' => false
            ],
            [
                'key' => 'upload_min_duration',
                'value' => $this->toValue(30),
                'type' => 'integer',
                'name' => 'Media Minimum duration',
                'description' => 'Upload media duration should be longer then the configured amount of seconds',
                'group' => 'uploads',
                'order' => 30,
                'validation' => '',
                'readonly' => false
            ],
            [
                'key' => 'upload_max_duration',
                'value' => $this->toValue(240),
                'type' => 'integer',
                'name' => 'Media Maximum duration',
                'description' => 'Uploaded media duration cannot be longer then the configured amount of seconds',
                'group' => 'uploads',
                'order' => 40,
                'validation' => '',
                'readonly' => false
            ]
        ]);
    }

    /**
     * Will transform the input data into the properly formatted JSON string
     * for storing in the Database
     * @param $value
     * @return false|string
     */
    private function toValue($value) {
        return json_encode(['data' => $value]);
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
