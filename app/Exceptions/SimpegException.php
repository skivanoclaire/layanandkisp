<?php

namespace App\Exceptions;

use RuntimeException;

class SimpegException extends RuntimeException
{
    public int $status;
    public array|string|null $payload;

    public function __construct(string $message, int $status = 0, array|string|null $payload = null)
    {
        parent::__construct($message, $status);
        $this->status  = $status;
        $this->payload = $payload;
    }
}
