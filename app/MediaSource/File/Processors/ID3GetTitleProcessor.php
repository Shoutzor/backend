<?php

namespace App\MediaSource\File\Processors;

use App\MediaSource\Base\Processors\Processor;
use App\MediaSource\Base\Processors\ProcessorError;
use App\MediaSource\ProcessorItem;
use Closure;
use getID3;

class ID3GetTitleProcessor extends Processor
{

    /**
     * Will attempt to determine the filename from the ID3 tags
     * This is not a recommended way of determining the file information
     *
     * Audio fingerprinting is the recommended way of determining what track it is
     *
     * @param ProcessorItem $item
     * @param Closure $next
     * @return mixed|void
     */
    public function handle(ProcessorItem $item, Closure $next) {
        $upload = $item->getUpload();
        $media = $item->getMedia();

        $id3 = new getID3();
        $id3info = $id3->analyze($upload->getFilePath());

        # Iterate over tags, return the resulting array
        $id3tags = $this->parseTags($id3info);

        if(array_key_exists('title', $id3tags)) {
            $media->title = $id3tags['title'][0];
        }

        // File exists, continue processing
        $next($item);
    }

    private function parseTags(array $id3info): array
    {
        $result = [];

        if(array_key_exists('tags', $id3info)) {
            $result = array_values($id3info['tags'])[0];
        }

        return $result;
    }
}