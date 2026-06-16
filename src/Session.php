<?php

declare(strict_types=1);

namespace Componenta\Session;

final class Session implements SessionInterface
{
    public function __construct(
        public SessionId $id,
        private(set) readonly SessionData $data = new SessionData,
        private(set) readonly SessionMetadata $metadata = new SessionMetadata,
    ) {}
}
