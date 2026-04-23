<?php

namespace App\Exceptions;

use Throwable;

class AuthRepositoryError extends RepositoryError
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        string $message,
        array $details = [],
        int $status = 500,
        ?Throwable $previous = null,
    ) {
        parent::__construct('Authentication Repository Error', $message, $details, $status, $previous);
    }

    public function causeBy(Throwable $previous): static
    {
        return new static($this->getMessage(), $this->details(), $this->status(), $previous);
    }
}