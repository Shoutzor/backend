<?php

namespace App\MediaSource\Base\Processors;

use App\MediaSource\ProcessorItem;
use Closure;

abstract class Processor
{

    /**
     * This function will be called once processing has started and
     * its the turn of this Processor.
     *
     * Processor classes should be kept small and each with a specific task
     * ie: check if a file exists; check if the hash exists; etc.
     *
     * This function should only contain validation logic, or
     * logic related to the Upload.
     *
     * It should NOT create any artists, albums, etc. This logic
     * should be added to the Agent instead.
     *
     * @param ProcessorItem $item
     * @param Closure $next
     * @return mixed
     */
    public abstract function handle(ProcessorItem $item, Closure $next);

}