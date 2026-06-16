<?php

declare(strict_types=1);

namespace Componenta\Session\Exception;

use Componenta\Session\SessionId;

class StorageException extends SessionException
{
    public static function readFailed(SessionId $id, ?\Throwable $previous = null): self
    {
        return new self("Failed to read session '{$id->value}'", 0, $previous);
    }

    public static function writeFailed(SessionId $id, ?\Throwable $previous = null): self
    {
        return new self("Failed to write session '{$id->value}'", 0, $previous);
    }

    public static function destroyFailed(SessionId $id, ?\Throwable $previous = null): self
    {
        return new self("Failed to destroy session '{$id->value}'", 0, $previous);
    }

    public static function gcFailed(?\Throwable $previous = null): self
    {
        return new self('Failed to perform garbage collection', 0, $previous);
    }
}
