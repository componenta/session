<?php

declare(strict_types=1);

namespace Componenta\Session\Storage;

use Componenta\Session\Exception\StorageException;
use Componenta\Session\SessionId;

final class NativeSessionHandler implements SessionStorageInterface
{
    private bool $opened = false;

    public function __construct(
        private(set) readonly \SessionHandlerInterface $handler,
        private readonly string $savePath = '',
        private readonly string $sessionName = 'PHPSESSID',
    ) {}

    public function read(SessionId $id): ?string
    {
        $this->ensureOpened();

        $data = $this->handler->read($id->value);

        if ($data === '' || $data === false) {
            return null;
        }

        return $data;
    }

    public function write(SessionId $id, string $data, int $ttl): void
    {
        $this->ensureOpened();

        $result = $this->handler->write($id->value, $data);

        if ($result === false) {
            throw StorageException::writeFailed($id);
        }
    }

    public function destroy(SessionId $id): void
    {
        $this->ensureOpened();

        $result = $this->handler->destroy($id->value);

        if ($result === false) {
            throw StorageException::destroyFailed($id);
        }
    }

    public function exists(SessionId $id): bool
    {
        return $this->read($id) !== null;
    }

    public function gc(int $maxLifetime): int
    {
        $this->ensureOpened();

        $result = $this->handler->gc($maxLifetime);

        if ($result === false) {
            throw StorageException::gcFailed();
        }

        return is_int($result) ? $result : 0;
    }

    private function ensureOpened(): void
    {
        if ($this->opened) {
            return;
        }

        $result = $this->handler->open($this->savePath, $this->sessionName);

        if ($result === false) {
            throw new StorageException('Failed to open session handler');
        }

        $this->opened = true;
    }

    public function __destruct()
    {
        if ($this->opened) {
            $this->handler->close();
        }
    }
}
