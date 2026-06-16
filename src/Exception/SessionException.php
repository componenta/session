<?php

declare(strict_types=1);

namespace Componenta\Session\Exception;

class SessionException extends \RuntimeException implements SessionExceptionInterface
{
    public static function startFailed(string $reason, ?\Throwable $previous = null): self
    {
        return new self("Failed to start session: $reason", 0, $previous);
    }

    public static function saveFailed(string $reason, ?\Throwable $previous = null): self
    {
        return new self("Failed to save session: $reason", 0, $previous);
    }
}
