<?php

namespace App\MediaSource\File;

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
            ID3GetTitleProcessor::class,
            IdentifyMusicProcessor::class
        ];
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
