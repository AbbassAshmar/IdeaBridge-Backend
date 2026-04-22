<?php

namespace App\Exceptions;

use Throwable;

class ApplicationError extends \Exception
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        protected string $title,
        string $message,
        protected array $details = [],
        protected int $status = 500,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function title(): string
    {
        return $this->title;
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return $this->details;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function causeBy(Throwable $previous): static
    {
        return new static($this->title, $this->getMessage(), $this->details, $this->status, $previous);
    }

    /**
     * @return array<string, mixed>
     */
    public function toResponseError(): array
    {
        $details = $this->details;
        $previous = $this->getPrevious();

        if ($previous instanceof self) {
            $details['cause'] = $previous->toResponseError();
        } elseif ($previous instanceof Throwable) {
            $details['cause'] = [
                'title' => class_basename($previous),
                'message' => $previous->getMessage(),
            ];
        }

        return [
            'title' => $this->title,
            'message' => $this->getMessage(),
            'details' => (object) $details,
        ];
    }
}