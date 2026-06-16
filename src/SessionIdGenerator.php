<?php

declare(strict_types=1);

namespace Componenta\Session;

final class SessionIdGenerator implements SessionIdGeneratorInterface
{
    public function __construct(
        private readonly int $length = 32,
    ) {}

    public function generate(): SessionId
    {
        return new SessionId(bin2hex(random_bytes($this->length)));
    }
}
