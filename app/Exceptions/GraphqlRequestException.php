<?php

namespace App\Exceptions;

use Exception;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;

class GraphqlRequestException extends Exception implements RendersErrorsExtensions
{

    public function isClientSafe(): bool {
        return true;
    }

    /**
     * Returns string describing a category of the error.
     *
     * @api
     * @return string
     */
    public function getCategory(): string
    {
        return 'shoutzor';
    }

    public function extensionsContent(): array
    {
        return [
            'message' => $this->message
        ];
    }
}