<?php

namespace App\MediaSource\File;

use App\Helpers\ShoutzorSetting;
use App\MediaSource\AcoustID\Processors\IdentifyMusicProcessor;
use App\MediaSource\Base\MediaSource as BaseMediaSource;
use App\MediaSource\File\Processors\FileExistsProcessor;
use App\MediaSource\File\Processors\ID3GetTitleProcessor;
use App\MediaSource\File\Processors\MediaDurationProcessor;
use App\MediaSource\File\Processors\MediaFileHashProcessor;

class MediaSource extends BaseMediaSource
{
    public function __construct() {
        parent::__construct();

        $this->identifier = 'file';
        $this->name = 'file';
        $this->icon = '';
        $this->agents = [];
        $this->processors = [
            FileExistsProcessor::class,
            MediaFileHashProcessor::class,
            MediaDurationProcessor::class,
            // Based on whether AcoustID is enabled we want to identify
            // a song based on the audio fingerprinting vs. the ID3 tags
            (
            ShoutzorSetting::getSetting('acoustid_enabled') ?
                ID3GetTitleProcessor::class :
                IdentifyMusicProcessor::class
            )
        ];
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
