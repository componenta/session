<?php

declare(strict_types=1);

namespace Componenta\Session\Storage;

use Componenta\Session\Exception\StorageException;
use Componenta\Session\SessionId;

interface SessionStorageInterface
{
    /**
     * Reads raw session data.
     *
     * @return string|null Raw data or null if session doesn't exist
     *
     * @throws StorageException When read operation fails
     */
    public function read(SessionId $id): ?string;

    /**
     * Writes raw session data.
     *
     * @param positive-int $ttl Time-to-live in seconds
     *
     * @throws StorageException When write operation fails
     */
    public function write(SessionId $id, string $data, int $ttl): void;

    /**
     * Destroys session data.
     *
     * @throws StorageException When destroy operation fails
     */
    public function destroy(SessionId $id): void;

    /**
     * Checks if session exists.
     */
    public function exists(SessionId $id): bool;

    /**
     * Performs garbage collection.
     *
     * @param positive-int $maxLifetime Maximum lifetime in seconds
     *
     * @return int Number of deleted sessions
     *
     * @throws StorageException When GC fails
     */
    public function gc(int $maxLifetime): int;
}
