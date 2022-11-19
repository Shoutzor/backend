<?php

namespace App\Observers;

use App\Helpers\ShoutzorSetting;
use App\Models\Setting;

class SettingObserver {
    /**
     * Handle events after all transactions are committed.
     *
     * @var bool
     */
    public $afterCommit = true;

    public function updated(Setting $setting) {
        ShoutzorSetting::updateCache($setting->key, ShoutzorSetting::createCacheData($setting));
    }
}