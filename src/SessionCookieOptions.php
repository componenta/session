<?php

declare(strict_types=1);

namespace Componenta\Session;

readonly class SessionCookieOptions
{
    /**
     * @param non-empty-string $name Cookie name
     * @param int<0, max>|null $lifetime Null = session cookie (expires when browser closes)
     */
    public function __construct(
        public string $name = 'Componenta_SESSID',
        public string $path = '/',
        public string $domain = '',
        public bool $secure = true,
        public bool $httpOnly = true,
        public SameSite $sameSite = SameSite::Lax,
        public ?int $lifetime = null,
    ) {}
}
