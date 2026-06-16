<?php

declare(strict_types=1);

namespace Componenta\Session\Cookie;

use Componenta\Session\SessionCookieOptions;
use Componenta\Session\SessionId;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface SessionCookieManagerInterface
{
    public function read(ServerRequestInterface $request, string $cookieName): ?SessionId;

    public function write(
        ResponseInterface $response,
        SessionId $id,
        SessionCookieOptions $options,
    ): ResponseInterface;

    public function clear(
        ResponseInterface $response,
        SessionCookieOptions $options,
    ): ResponseInterface;
}
