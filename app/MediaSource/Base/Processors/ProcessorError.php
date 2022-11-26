<?php

namespace App\MediaSource\Base\Processors;

use Throwable;

/**
 * Used to indicate an error by one of the Processors
 * will contain the error, whether the upload is rejected, and
 * optionally an exception object related to the error.
 */
class ProcessorError {

    public function __construct(
        public readonly string $error,
        private ?Throwable $exception = null,
        public readonly bool $rejected = true
    ) {}

    public function getException(): ?Throwable
    {
        return $this->exception;
    }

}