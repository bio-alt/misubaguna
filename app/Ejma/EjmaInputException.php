<?php

namespace App\Ejma;

final class EjmaInputException extends \DomainException
{
    public function __construct(public readonly string $field, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
