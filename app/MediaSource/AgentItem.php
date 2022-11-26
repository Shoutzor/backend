<?php

namespace App\MediaSource;

use App\Models\Media;
use App\Models\Upload;

class AgentItem
{
    public function __construct(
        private readonly Media  $media
    ) {
    }

    public function getMedia() : Media {
        return $this->media;
    }

}