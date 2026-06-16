<?php

declare(strict_types=1);

namespace Componenta\Session\Tests;

use Componenta\Session\Exception\AttributeNotFoundException;
use Componenta\Session\Exception\AttributeTypeMismatchException;
use Componenta\Session\SessionData;
use PHPUnit\Framework\TestCase;

final class SessionDataTest extends TestCase
{
    public function testStoresAndReadsAttributes(): void
    {
        $data = new SessionData(['name' => 'Ada', 'count' => 1]);

        self::assertSame('Ada', $data->getString('name'));
        self::assertSame(1, $data->getInt('count'));
        self::assertTrue($data->has('name'));
        self::assertSame(['name' => 'Ada'], $data->only('name'));
        self::assertSame(['count' => 1], $data->except('name'));
    }

    public function testThrowsForMissingAndMismatchedAttributes(): void
    {
        $data = new SessionData(['name' => 'Ada']);

        $this->expectException(AttributeTypeMismatchException::class);
        $data->getInt('name');
    }

    public function testPullRemovesAttribute(): void
    {
        $data = new SessionData(['token' => 'abc']);

        self::assertSame('abc', $data->pull('token'));
        self::assertFalse($data->has('token'));
    }

    public function testMissingAttributeThrowsWithoutDefault(): void
    {
        $this->expectException(AttributeNotFoundException::class);

        (new SessionData())->get('missing');
    }

    public function testFlashMessagesAreSerializedForNextRequest(): void
    {
        $data = new SessionData();
        $data->flash('success', 'Saved');

        self::assertSame(['Saved'], $data->peekFlashes('success'));

        $restored = SessionData::fromArray($data->toArray());

        self::assertSame(['Saved'], $restored->getFlashes('success'));
        self::assertSame([], $restored->getFlashes('success'));
    }
}
