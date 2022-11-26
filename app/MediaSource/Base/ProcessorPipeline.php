<?php

namespace App\MediaSource\Base;

use App\MediaSource\Base\Processors\ProcessorError;
use Closure;
use Illuminate\Pipeline\Pipeline;
use Throwable;

/**
 * This extended pipeline catches any exceptions that occur during each slice.
 *
 * The exceptions are converted to HTTP responses for proper middleware handling.
 */
class ProcessorPipeline extends Pipeline
{
    private ?Closure $errorHandler = null;

    /**
     * Handles the value returned from each pipe before passing it to the next.
     *
     * @param  mixed  $carry
     * @return mixed
     */
    protected function handleCarry($carry) : mixed
    {
        // Check if any of the processors returned an error
        if($carry instanceof ProcessorError) {
            $this->handleProcessorError($carry);
            return $carry;
        }
        // Otherwise, just continue the chain
        else {
            return $carry;
        }
    }

    protected function handleException($passable, Throwable $e)
    {
        $this->handleProcessorError(new ProcessorError(
            $e->getMessage(),
            $e,
            false
        ));
    }

    private function handleProcessorError(ProcessorError $error) {
        $handler = $this->errorHandler;
        if($handler !== null) {
            $handler($error);
        }
    }

    public function onError(Closure $handler)
    {
        $this->errorHandler = $handler;
        return $this;
    }
}
