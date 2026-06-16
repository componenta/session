<?php

declare(strict_types=1);

namespace Componenta\Session\Serializer;

use Componenta\Session\Exception\SerializationException;

final class PhpSerializer implements SessionSerializerInterface
{
    public function serialize(array $data): string
    {
        try {
            return serialize($data);
        } catch (\Throwable $e) {
            throw SerializationException::encodeFailed($e->getMessage(), $e);
        }
    }

    public function unserialize(string $data): array
    {
        if ($data === '') {
            return [];
        }

        try {
            $result = unserialize($data, ['allowed_classes' => true]);
        } catch (\Throwable $e) {
            throw SerializationException::decodeFailed($e->getMessage(), $e);
        }

        if (!is_array($result)) {
            throw SerializationException::decodeFailed('Expected array, got ' . get_debug_type($result));
        }

        return $result;
    }
}
