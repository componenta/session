<?php

declare(strict_types=1);

namespace Componenta\Session\Serializer;

use Componenta\Session\Exception\SerializationException;

interface SessionSerializerInterface
{
    /**
     * @param array<string, mixed> $data
     *
     * @throws SerializationException
     */
    public function serialize(array $data): string;

    /**
     * @return array<string, mixed>
     *
     * @throws SerializationException
     */
    public function unserialize(string $data): array;
}
