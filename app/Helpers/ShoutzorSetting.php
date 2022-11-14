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
     * Fetches the provided key from the cache
     * if the item does not exist in cache yet it will
     * fetch it from the database instead, and cache
     * the resulting value
     * @param string $key
     * @return string
     */
    private static function getSetting($key) : string {
        $val = Cache::get(ShoutzorSetting::CACHE_KEY_PREFIX . $key);

        // Check if the item exists in cache
        if($val === null) {
            // Item does not exist in cache yet, fetch & store
            $dbVal = Setting::find($key)->value;
            static::updateCache($key, $dbVal);

            // Return value from database
            return $dbVal;
        }

        // Cached item exists, return value
        return $val;
    }

    public static function updateCache($key, $value): void
    {
        Cache::forever(ShoutzorSetting::CACHE_KEY_PREFIX . $key, $value);
    }

    /**
     * if a user account requires email verification
     */
    public static function isEmailVerificationRequired() : bool {
        return static::getSetting('user_must_verify_email') === 'true';
    }

    /**
     * if a user account requires manual approval
     */
    public static function isManualApproveRequired() : bool {
        return static::getSetting('user_manual_approve_required') === 'true';
    }

    /**
     * Returns the installed version of shoutzor (from the Database)
     */
    public static function version() : string {
        return static::getSetting('version');
    }

    public static function emailVerificationUrl() : string {
        return config('shoutzor.frontend_url') . '/?action=verify-email&id=__ID__&hash=__HASH__&exp=__EXPIRES__&sig=__SIGNATURE__';
    }

    public static function resetPasswordUrl() : string {
        return config('shoutzor.frontend_url') . '/?action=reset-password&email=__EMAIL__&token=__TOKEN__';
    }
}