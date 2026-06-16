<?php

declare(strict_types=1);

namespace Componenta\Session\Middleware;

use Componenta\Session\Cookie\SessionCookieManager;
use Componenta\Session\Cookie\SessionCookieManagerInterface;
use Componenta\Session\SessionCookieOptions;
use Componenta\Session\SessionInterface;
use Componenta\Session\SessionManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class SessionMiddleware implements MiddlewareInterface
{
    public const string REQUEST_ATTRIBUTE = SessionInterface::class;

    public function __construct(
        private readonly SessionManagerInterface $sessionManager,
        private readonly SessionCookieOptions $cookieOptions = new SessionCookieOptions(),
        private readonly SessionCookieManagerInterface $cookieManager = new SessionCookieManager(),
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $sessionId = $this->cookieManager->read($request, $this->cookieOptions->name);
        $session = $this->sessionManager->start($sessionId);

        $request = $request->withAttribute(self::REQUEST_ATTRIBUTE, $session);

        $response = $handler->handle($request);

        $this->sessionManager->save($session);

        $response = $this->cookieManager->write($response, $session->id, $this->cookieOptions);

        $this->maybeRunGc();

        return $response;
    }

    private function maybeRunGc(): void
    {
        $probability = $this->sessionManager->getConfig()->gcProbability;

        if ($probability <= 0) {
            return;
        }

        if ($probability >= 100 || random_int(1, 100) <= $probability) {
            $this->sessionManager->gc();
        }
    }
}
