<?php

declare(strict_types=1);

namespace Componenta\Session\Exception;

class AttributeNotFoundException extends \OutOfBoundsException implements SessionExceptionInterface
{
    public static function forKey(string $key): self
    {
        return new self("Session attribute '$key' not found");
    }
}
