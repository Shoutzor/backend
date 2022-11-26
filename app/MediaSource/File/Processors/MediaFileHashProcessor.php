<?php

namespace App\MediaSource\File\Processors;

use App\MediaSource\Base\Processors\Processor;
use App\MediaSource\Base\Processors\ProcessorError;
use App\MediaSource\ProcessorItem;
use App\Models\Media;
use Closure;

class MediaFileHashProcessor extends Processor
{

    /**
     * Calculates a file hash and will check if the hash already exists
     * @param ProcessorItem $item
     * @param Closure $next
     * @return mixed|void
     */
    public function handle(ProcessorItem $item, Closure $next) {
        $upload = $item->getUpload();
        $media = $item->getMedia();

        // Calculate the file hash
        $media->hash = hash_file('sha512', $upload->getFilePath());

        // Check if the hash already exists in the database
        $exist = Media::query()->where('hash', $media->hash)->first();

        if ($exist) {
            return new ProcessorError("A media file with the same hash is already in the database");
        }

        // File exists, continue processing
        $next($item);
    }
}