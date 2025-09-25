<?php

namespace App\Exceptions;

use Exception;

class RateLimitExceededException extends Exception
{
    public function __construct(
        public readonly array $errors,
        public readonly int $retryAfterSeconds,
        string $message = 'Rate limit exceeded',
        int $code = 429,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the errors for the response.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the retry-after header value in seconds.
     */
    public function getRetryAfterSeconds(): int
    {
        return $this->retryAfterSeconds;
    }
}
