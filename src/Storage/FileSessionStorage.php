<?php

declare(strict_types=1);

namespace Componenta\Session\Storage;

use Componenta\Session\Exception\StorageException;
use Componenta\Session\SessionId;

final readonly class FileSessionStorage implements SessionStorageInterface
{
    public function __construct(
        private string $savePath,
        private string $prefix = 'sess_',
    ) {
        if (!is_dir($this->savePath)) {
            if (!mkdir($this->savePath, 0700, true) && !is_dir($this->savePath)) {
                throw new StorageException("Failed to create session save path: {$this->savePath}");
            }
        }
    }

    public function read(SessionId $id): ?string
    {
        $path = $this->getPath($id);

        if (!file_exists($path)) {
            return null;
        }

        $data = file_get_contents($path);

        if ($data === false) {
            throw StorageException::readFailed($id);
        }

        return $data;
    }

    public function write(SessionId $id, string $data, int $ttl): void
    {
        $path = $this->getPath($id);

        if (file_put_contents($path, $data, LOCK_EX) === false) {
            throw StorageException::writeFailed($id);
        }

        touch($path, time() + $ttl);
    }

    public function destroy(SessionId $id): void
    {
        $path = $this->getPath($id);

        if (file_exists($path) && !unlink($path)) {
            throw StorageException::destroyFailed($id);
        }
    }

    public function exists(SessionId $id): bool
    {
        $path = $this->getPath($id);

        if (!file_exists($path)) {
            return false;
        }

        $mtime = filemtime($path);

        return $mtime !== false && $mtime > time();
    }

    public function gc(int $maxLifetime): int
    {
        $count = 0;
        $now = time();
        $pattern = $this->savePath . DIRECTORY_SEPARATOR . $this->prefix . '*';

        foreach (glob($pattern) as $file) {
            if (!is_file($file)) {
                continue;
            }

            $mtime = filemtime($file);

            if ($mtime !== false && $mtime < $now) {
                if (unlink($file)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    private function getPath(SessionId $id): string
    {
        return $this->savePath . DIRECTORY_SEPARATOR . $this->prefix . $id->value;
    }
}
