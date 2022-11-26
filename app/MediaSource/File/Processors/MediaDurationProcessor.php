<?php

namespace App\MediaSource\File\Processors;

use App\Helpers\ShoutzorSetting;
use App\MediaSource\Base\Processors\Processor;
use App\MediaSource\Base\Processors\ProcessorError;
use App\MediaSource\ProcessorItem;
use App\Models\Upload;
use Closure;
use FFMpeg\FFProbe;

class MediaDurationProcessor extends Processor
{
    /**
     * Calculates a file hash and will check if the hash already exists
     * @param ProcessorItem $item
     * @param Closure $next
     * @return ProcessorError|void
     */
    public function handle(ProcessorItem $item, Closure $next) {
        $upload = $item->getUpload();
        $media = $item->getMedia();

        // Use FFProbe to determine the duration of the media file (could be both audio & video)
        // using `intval` to convert to seconds and round it down.
        $ffprobe = FFProbe::create();
        $duration = intval(
            $ffprobe->format($upload->getFilePath())->get('duration')
        );

        $minDuration = ShoutzorSetting::uploadMinimumDuration();
        $maxDuration = ShoutzorSetting::uploadMaximumDuration();

        if($duration === 0) {
            return new ProcessorError("Failed to determine the duration of the file");
        }
        elseif(
            $minDuration > 0 &&
            $duration < $minDuration
        ) {
            return new ProcessorError("File duration is shorter then the configured minimum duration");
        }
        elseif(
            $maxDuration > 0 &&
            $duration > $maxDuration
        ) {
            return new ProcessorError("File duration is longer then the configured maximum duration");
        }

        $media->duration = $duration;

        // File exists, continue processing
        $next($item);
    }
}