<?php

declare(strict_types=1);

namespace Componenta\Session\Cookie;

use Componenta\Session\SessionCookieOptions;
use Componenta\Session\SessionId;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SessionCookieManager implements SessionCookieManagerInterface
{
    public function read(ServerRequestInterface $request, string $cookieName): ?SessionId
    {
        $cookies = $request->getCookieParams();

        if (!isset($cookies[$cookieName]) || !is_string($cookies[$cookieName]) || $cookies[$cookieName] === '') {
            return null;
        }

        return new SessionId($cookies[$cookieName]);
    }

    public function write(
        ResponseInterface $response,
        SessionId $id,
        SessionCookieOptions $options,
    ): ResponseInterface {
        $cookie = $this->buildCookie($options->name, $id->value, $options);

        return $response->withAddedHeader('Set-Cookie', $cookie);
    }

    public function clear(
        ResponseInterface $response,
        SessionCookieOptions $options,
    ): ResponseInterface {
        $expiredOptions = new SessionCookieOptions(
            name: $options->name,
            path: $options->path,
            domain: $options->domain,
            secure: $options->secure,
            httpOnly: $options->httpOnly,
            sameSite: $options->sameSite,
            lifetime: -3600,
        );

        $cookie = $this->buildCookie($options->name, '', $expiredOptions);

        return $response->withAddedHeader('Set-Cookie', $cookie);
    }

    private function buildCookie(string $name, string $value, SessionCookieOptions $options): string
    {
        $parts = [
            sprintf('%s=%s', urlencode($name), urlencode($value)),
        ];

        if ($options->lifetime !== null) {
            $expires = time() + $options->lifetime;
            $parts[] = sprintf('Expires=%s', gmdate('D, d M Y H:i:s T', $expires));
            $parts[] = sprintf('Max-Age=%d', $options->lifetime);
        }

        if ($options->path !== '') {
            $parts[] = sprintf('Path=%s', $options->path);
        }

        if ($options->domain !== '') {
            $parts[] = sprintf('Entity=%s', $options->domain);
        }

        if ($options->secure) {
            $parts[] = 'Secure';
        }

        if ($options->httpOnly) {
            $parts[] = 'HttpOnly';
        }

        $parts[] = sprintf('SameSite=%s', $options->sameSite->value);

        return implode('; ', $parts);
    }
}
