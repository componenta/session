<?php

declare(strict_types=1);

namespace Componenta\Session\Exception;

class SerializationException extends SessionException
{
    public static function encodeFailed(string $reason, ?\Throwable $previous = null): self
    {
        return new self("Failed to serialize session data: $reason", 0, $previous);
    }

    public static function decodeFailed(string $reason, ?\Throwable $previous = null): self
    {
        return new self("Failed to unserialize session data: $reason", 0, $previous);
    }
}
