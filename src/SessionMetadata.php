<?php

declare(strict_types=1);

namespace Componenta\Session;

final class SessionMetadata
{
    public function __construct(
        public \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        public \DateTimeImmutable $lastUsedAt = new \DateTimeImmutable(),
    ) {}

    public function touch(): void
    {
        $this->lastUsedAt = new \DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'created_at' => $this->createdAt->getTimestamp(),
            'last_used_at' => $this->lastUsedAt->getTimestamp(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            new \DateTimeImmutable()->setTimestamp($data['created_at'] ?? time()),
            new \DateTimeImmutable()->setTimestamp($data['last_used_at'] ?? time()),
        );
    }
}
