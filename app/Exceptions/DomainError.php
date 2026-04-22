<?php

namespace App\Exceptions;

use Throwable;

class DomainError extends ApplicationError
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        string $title,
        string $message,
        array $details = [],
        int $status = 422,
        ?Throwable $previous = null,
    ) {
        parent::__construct($title, $message, $details, $status, $previous);
    }
}