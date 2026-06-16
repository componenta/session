<?php

declare(strict_types=1);

namespace Componenta\Session\Serializer;

use Componenta\Session\Exception\SerializationException;

final readonly class JsonSerializer implements SessionSerializerInterface
{
    public function __construct(
        private int $encodeFlags = JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION,
        private int $decodeFlags = JSON_THROW_ON_ERROR,
    ) {}

    public function serialize(array $data): string
    {
        try {
            return json_encode($data, $this->encodeFlags);
        } catch (\JsonException $e) {
            throw SerializationException::encodeFailed($e->getMessage(), $e);
        }
    }

    public function unserialize(string $data): array
    {
        if ($data === '') {
            return [];
        }

        try {
            $result = json_decode($data, true, 512, $this->decodeFlags);
        } catch (\JsonException $e) {
            throw SerializationException::decodeFailed($e->getMessage(), $e);
        }

        if (!is_array($result)) {
            throw SerializationException::decodeFailed('Expected array, got ' . get_debug_type($result));
        }

        return $result;
    }
}
