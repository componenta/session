<?php

declare(strict_types=1);

namespace Componenta\Session;

use Componenta\Session\Exception\SessionException;

interface SessionManagerInterface
{
    /**
     * Starts new session or resumes existing one.
     *
     * @throws SessionException
     */
    public function start(?SessionId $id = null): SessionInterface;

    /**
     * Saves session data to storage.
     *
     * @throws SessionException
     */
    public function save(SessionInterface $session): void;

    /**
     * Destroys session and its data.
     *
     * @throws SessionException
     */
    public function destroy(SessionInterface $session): void;

    /**
     * Destroys multiple sessions by IDs.
     *
     * @throws SessionException
     */
    public function destroyMany(SessionId ...$ids): void;

    /**
     * Regenerates session ID.
     *
     * @param bool $deleteOld Delete old session data
     */
    public function regenerate(SessionInterface $session, bool $deleteOld = true): void;

    /**
     * Performs garbage collection.
     *
     * @return int Number of deleted sessions
     */
    public function gc(): int;

    public function getConfig(): SessionConfig;
}
