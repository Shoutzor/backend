<?php

namespace App\MediaSource\File\Processors;

use App\MediaSource\Base\Processors\Processor;
use App\MediaSource\Base\Processors\ProcessorError;
use App\MediaSource\ProcessorItem;
use Closure;

class FileExistsProcessor extends Processor
{

    /**
     * Checks if a file still exists in the temporary directory before processing
     * @param ProcessorItem $item
     * @param Closure $next
     * @return mixed|void
     */
    public function handle(ProcessorItem $item, Closure $next) {
        $uploadFile = $item->getUpload()->getFilePath();

        if(!file_exists($uploadFile)) {
            return new ProcessorError("$uploadFile could not be found");
        }

        // File exists, continue processing
        $next($item);
    }

}