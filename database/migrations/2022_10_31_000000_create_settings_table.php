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
                'key' => 'shoutzor_player_token',
                'value' => $this->toValue('Y0urPl4yerP4sswordH3re!'),
                'type' => 'string',
                'name' => 'Shoutz0r Player Auth Token',
                'description' => 'A token that the Shoutz0r player can use to authenticate itself. Make sure the player is configured to use this same token.',
                'group' => 'shoutzor',
                'order' => 20,
                'validation' => '',
                'readonly' => false
            ],
            [
                'key' => 'shoutzor_request_user_delay',
                'value' => $this->toValue(10),
                'type' => 'integer',
                'name' => 'Request User delay',
                'description' => 'The time (in minutes) before a user is allowed to make another request',
                'group' => 'shoutzor',
                'order' => 30,
                'validation' => '',
                'readonly' => false
            ],
            [
                'key' => 'shoutzor_request_media_delay',
                'value' => $this->toValue(60),
                'type' => 'integer',
                'name' => 'Request Media delay',
                'description' => 'The time (in minutes) when a media file can be played again',
                'group' => 'shoutzor',
                'order' => 50,
                'validation' => '',
                'readonly' => false
            ],
            [
                'key' => 'shoutzor_request_artist_delay',
                'value' => $this->toValue(30),
                'type' => 'integer',
                'name' => 'Request Artist delay',
                'description' => 'The time (in minutes) when an artist can be played again',
                'group' => 'shoutzor',
                'order' => 50,
                'validation' => '',
                'readonly' => false
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
                'description' => 'The maximum allowed filesize for uploads (in MB); If set to 0 this will be ignored. Make sure the backend is configured to accept the upload size too.',
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
                'description' => 'Uploaded media will be required to have a minimum duration (in seconds); If 0 this is ignored. This will not affect files that have already been processed. This does not affect duration requirements for requests.',
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
                'description' => 'Uploaded media will be limited to a maximum duration (in seconds); If 0 this is ignored. This will not affect files that have already been processed. This does not affect duration requirements for requests.',
                'group' => 'uploads',
                'order' => 40,
                'validation' => '',
                'readonly' => false
            ],
            [
                'key' => 'acoustid_enabled',
                'value' => $this->toValue(false),
                'type' => 'boolean',
                'name' => 'Enable AcoustID Audio Fingerprinting',
                'description' => 'Whether AcoustID Audio Fingerprinting should be used to identify whats uploaded',
                'group' => 'acoustid',
                'order' => 10,
                'validation' => '',
                'readonly' => false
            ],
            [
                'key' => 'acoustid_required',
                'value' => $this->toValue(false),
                'type' => 'boolean',
                'name' => 'Require AcoustID Recognition',
                'description' => 'Should a song be rejected if it is not recognized by AcoustID',
                'group' => 'acoustid',
                'order' => 20,
                'validation' => '',
                'readonly' => false
            ],
            [
                'key' => 'acoustid_apikey',
                'value' => $this->toValue(''),
                'type' => 'string',
                'name' => 'AcoustID API Key',
                'description' => 'The AcoustID API Key to use',
                'group' => 'acoustid',
                'order' => 30,
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
