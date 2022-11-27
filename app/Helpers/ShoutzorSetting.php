<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Helper class that fetches the shoutzor settings
 * from the database and implements caching
 */
class ShoutzorSetting {

    const CACHE_KEY_PREFIX = 'shoutzor.setting.';

    /**
     * Fetches the provided key from the cache if the item does not exist in cache it will
     * fetch it from the database instead, and cache the resulting value.
     * Cached data gets automatically updated by the App\Observers\SettingObserver when changed.
     * @param string $key
     * @return mixed
     */
    public static function getSetting($key): mixed
    {
        $cachedObj = Cache::get(ShoutzorSetting::CACHE_KEY_PREFIX . $key);

        // Check if the item exists in cache
        if($cachedObj === null) {
            // Item does not exist in cache yet, fetch & store
            $cachedObj = static::createCacheData(Setting::findOrFail($key));
            static::updateCache($key, $cachedObj);
        }

        // Return the value
        return static::parseCacheData($cachedObj);
    }

    /**
     * Creates a cached object of the setting to store
     * @param Setting $setting
     * @return string
     */
    public static function createCacheData(Setting $setting) : string {
        return json_encode([
            'type' => $setting->type,
            'data' => $setting->value['data']
        ]);
    }

    /**
     * Parses the cached object and returns the data casted as the correct type
     * @param $data
     * @return mixed
     */
    private static function parseCacheData($data) : mixed {
        $content = json_decode($data);
        $data = $content->data;
        // only sets the type, does not output the new type.
        settype($data, $content->type);

        // Return the data
        return $data;
    }

    public static function updateCache($key, $value): void
    {
        Cache::forever(ShoutzorSetting::CACHE_KEY_PREFIX . $key, $value);
    }

    /**
     * Returns the minimum duration in seconds
     */
    public static function uploadMinimumDuration() : int {
        return static::getSetting('upload_min_duration');
    }

    /**
     * Returns the maximum duration in seconds
     */
    public static function uploadMaximumDuration() : int {
        return static::getSetting('upload_max_duration');
    }

    /**
     * Returns the maximum duration in seconds
     */
    public static function uploadAllowedExtensions() : array {
        return static::getSetting('upload_allowed_extensions');
    }

    /**
     * if a user account requires email verification
     */
    public static function isEmailVerificationRequired() : bool {
        return static::getSetting('user_must_verify_email');
    }

    /**
     * if a user account requires manual approval
     */
    public static function isManualApproveRequired() : bool {
        return static::getSetting('user_manual_approve_required');
    }

    /**
     * Returns the installed version of shoutzor (from the Database)
     */
    public static function version() : string {
        return static::getSetting('version');
    }

    /**
     * Returns the email verification URL template to use for generating the link in the email by sanctum
     * @return string
     */
    public static function emailVerificationUrl() : string {
        return config('shoutzor.frontend_url') . '/?action=verify-email&id=__ID__&hash=__HASH__&exp=__EXPIRES__&sig=__SIGNATURE__';
    }

    /**
     * Returns the reset password URL template to use for generating the link in the email by sanctum
     * @return string
     */
    public static function resetPasswordUrl() : string {
        return config('shoutzor.frontend_url') . '/?action=reset-password&email=__EMAIL__&token=__TOKEN__';
    }
}