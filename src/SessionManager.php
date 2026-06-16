<?php

declare(strict_types=1);

namespace Componenta\Session;

use Componenta\Session\Exception\SessionException;
use Componenta\Session\Serializer\PhpSerializer;
use Componenta\Session\Serializer\SessionSerializerInterface;
use Componenta\Session\Storage\SessionStorageInterface;

final class SessionManager implements SessionManagerInterface
{
    private const string METADATA_KEY = '_meta';
    private const string DATA_KEY = '_data';

    public function __construct(
        private readonly SessionStorageInterface $storage,
        private readonly SessionSerializerInterface $serializer = new PhpSerializer(),
        private readonly SessionIdGeneratorInterface $idGenerator = new SessionIdGenerator(),
        private readonly SessionConfig $config = new SessionConfig(),
    ) {}

    public function start(?SessionId $id = null): SessionInterface
    {
        if ($id === null) {
            return new Session(
                $this->idGenerator->generate()
            );
        }

        $rawData = $this->storage->read($id);

        if ($rawData === null) {
            return new Session($id);
        }

        try {
            $data = $this->serializer->unserialize($rawData);
        } catch (\Throwable $e) {
            throw SessionException::startFailed('Failed to unserialize session data', $e);
        }

        $session = $this->hydrate($id, $data);
        $session->metadata->touch();

        return $session;
    }

    public function save(SessionInterface $session): void
    {
        $data = $this->dehydrate($session);

        try {
            $rawData = $this->serializer->serialize($data);
        } catch (\Throwable $e) {
            throw SessionException::saveFailed('Failed to serialize session data', $e);
        }

        $this->storage->write($session->id, $rawData, $this->config->ttl);
    }

    public function destroy(SessionInterface $session): void
    {
        $this->storage->destroy($session->id);
        $session->data->clear();
    }

    public function destroyMany(SessionId ...$ids): void
    {
        foreach ($ids as $id) {
            $this->storage->destroy($id);
        }
    }

    public function regenerate(SessionInterface $session, bool $deleteOld = true): void
    {
        $oldId = $session->id;
        $newId = $this->idGenerator->generate();

        $session->id = $newId;

        if ($deleteOld) {
            $this->storage->destroy($oldId);
        }
    }

    public function gc(): int
    {
        return $this->storage->gc($this->config->gcMaxLifetime);
    }

    public function getConfig(): SessionConfig
    {
        return $this->config;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function hydrate(SessionId $id, array $data): Session
    {
        $metadata = isset($data[self::METADATA_KEY]) && is_array($data[self::METADATA_KEY])
            ? SessionMetadata::fromArray($data[self::METADATA_KEY])
            : new SessionMetadata();

        $sessionData = isset($data[self::DATA_KEY]) && is_array($data[self::DATA_KEY])
            ? SessionData::fromArray($data[self::DATA_KEY])
            : new SessionData();

        return new Session($id, $sessionData, $metadata);
    }

    /**
     * @return array<string, mixed>
     */
    private function dehydrate(SessionInterface $session): array
    {
        return [
            self::METADATA_KEY => $session->metadata->toArray(),
            self::DATA_KEY => $session->data->toArray(),
        ];
    }
}
