<?php

declare(strict_types=1);

namespace Componenta\Session\Exception;

class AttributeTypeMismatchException extends \UnexpectedValueException implements SessionExceptionInterface
{
    public static function expectedType(string $key, string $expected, string $actual): self
    {
        return new self("Session attribute '$key' expected to be $expected, got $actual");
    }
}
