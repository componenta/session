<?php

declare(strict_types=1);

namespace Componenta\Session;

interface SessionIdGeneratorInterface
{
    public function generate(): SessionId;
}
