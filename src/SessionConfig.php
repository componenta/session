<?php

declare(strict_types=1);

namespace Componenta\Session;

readonly class SessionConfig
{
    /**
     * @param positive-int $ttl Session TTL in seconds
     * @param positive-int $gcMaxLifetime GC max lifetime in seconds
     * @param int<0, 100> $gcProbability GC probability percentage (0 = never, 100 = always)
     */
    public function __construct(
        public int $ttl = 3600,
        public int $gcMaxLifetime = 3600,
        public int $gcProbability = 1,
    ) {}
}
