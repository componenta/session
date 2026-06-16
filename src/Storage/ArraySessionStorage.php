<?php

declare(strict_types=1);

namespace Componenta\Session\Storage;

use Componenta\Session\SessionId;

final class ArraySessionStorage implements SessionStorageInterface
{
    /** @var array<string, array{data: string, expires: int}> */
    private array $sessions = [];

    public function read(SessionId $id): ?string
    {
        if (!isset($this->sessions[$id->value])) {
            return null;
        }

        $session = $this->sessions[$id->value];

        if ($session['expires'] < time()) {
            unset($this->sessions[$id->value]);
            return null;
        }

        return $session['data'];
    }

    public function write(SessionId $id, string $data, int $ttl): void
    {
        $this->sessions[$id->value] = [
            'data' => $data,
            'expires' => time() + $ttl,
        ];
    }

    public function destroy(SessionId $id): void
    {
        unset($this->sessions[$id->value]);
    }

    public function exists(SessionId $id): bool
    {
        if (!isset($this->sessions[$id->value])) {
            return false;
        }

        if ($this->sessions[$id->value]['expires'] < time()) {
            unset($this->sessions[$id->value]);
            return false;
        }

        return true;
    }

    public function gc(int $maxLifetime): int
    {
        $count = 0;
        $now = time();

        foreach ($this->sessions as $key => $session) {
            if ($session['expires'] < $now) {
                unset($this->sessions[$key]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return array<string, array{data: string, expires: int}>
     */
    public function all(): array
    {
        return $this->sessions;
    }

    public function clear(): void
    {
        $this->sessions = [];
    }
}
