<?php

declare(strict_types=1);

namespace Componenta\Session\Tests;

use Componenta\Session\Exception\InvalidSessionIdException;
use Componenta\Session\SessionId;
use PHPUnit\Framework\TestCase;

final class SessionIdTest extends TestCase
{
    public function testStoresStringValueAndComparesIds(): void
    {
        $id = new SessionId('abc');

        self::assertSame('abc', $id->value);
        self::assertSame('abc', (string) $id);
        self::assertTrue($id->equals(new SessionId('abc')));
        self::assertFalse($id->equals(new SessionId('def')));
    }

    public function testRejectsEmptyId(): void
    {
        $this->expectException(InvalidSessionIdException::class);

        new SessionId('');
    }
}
