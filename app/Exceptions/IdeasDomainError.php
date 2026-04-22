<?php

namespace App\Exceptions;

use Throwable;

class IdeasDomainError extends DomainError
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        string $message,
        array $details = [],
        int $status = 422,
        ?Throwable $previous = null,
    ) {
        parent::__construct('Ideas Error', $message, $details, $status, $previous);
    }

    public function causeBy(Throwable $previous): static
    {
        return new static($this->getMessage(), $this->details(), $this->status(), $previous);
    }
}