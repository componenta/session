<?php

declare(strict_types=1);

namespace Componenta\Session;

interface SessionInterface
{

    public SessionId $id { get; set; }

    public SessionData $data { get; }

    public SessionMetadata $metadata { get; }
}
