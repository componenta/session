<?php

declare(strict_types=1);

namespace Componenta\Session;

use Componenta\Session\Exception\InvalidSessionIdException;

readonly class SessionId implements \Stringable
{
    private(set) string $value;

    public function __construct(string $value)
    {
        if ($value === '') {
            throw InvalidSessionIdException::empty();
        }

        $this->value = $value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
