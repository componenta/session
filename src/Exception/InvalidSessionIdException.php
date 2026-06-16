<?php

declare(strict_types=1);

namespace Componenta\Session\Exception;

class InvalidSessionIdException extends \InvalidArgumentException implements SessionExceptionInterface
{
    public static function empty(): self
    {
        return new self('Session ID cannot be empty');
    }

    public static function invalidFormat(string $id): self
    {
        return new self("Invalid session ID format: '$id'");
    }
}
