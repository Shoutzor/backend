<?php

namespace App\MediaSource\File\Processors;

use App\MediaSource\Base\Processors\Processor;
use App\MediaSource\Base\Processors\ProcessorError;
use App\MediaSource\ProcessorItem;
use Closure;
use getID3;

class MediaValidatePostProcessing extends Processor
{

    /**
     * Checks if the required fields of the media object are set
     * This should always be run last
     *
     * @param ProcessorItem $item
     * @param Closure $next
     * @return mixed|void
     */
    public function handle(ProcessorItem $item, Closure $next) {
        $media = $item->getMedia();

        if(empty($media->title)) {
            return new ProcessorError("could not determine title of media file");
        }

        // File exists, continue processing
        $next($item);
    }
}