# Componenta Session

Framework-agnostic session management for PHP 8.4+: session ids, session data, metadata, storage, serialization, cookies, manager lifecycle, and PSR-15 middleware.

Use this package when the application needs explicit session control instead of relying directly on PHP's global `$_SESSION` state.

## Installation

```bash
composer require componenta/session
```

## Requirements

- PHP 8.4+
- PSR-7 / PSR-15 for middleware integration

## Related Packages

| Package | Why it matters here |
|---|---|
| `componenta/auth` | Uses session ids for session authentication. |
| `psr/http-message` / `psr/http-server-middleware` | Session middleware reads cookies from PSR-7 requests and writes cookies to PSR-7 responses. |
| `componenta/pipeline` | Runs `SessionMiddleware` in the HTTP middleware chain. |

## Session Data

`SessionData` stores attributes and flash messages.

```php
use Componenta\Session\SessionData;

$data = new SessionData(['user_id' => 42]);

$data->getInt('user_id');     // 42
$data->set('theme', 'dark');
$data->pull('theme');         // "dark" and removes it
$data->only('user_id');
$data->except('csrf_token');
```

Typed getters throw `AttributeNotFoundException` when a required key is missing and `AttributeTypeMismatchException` when a value has the wrong type.

Flash messages are meant for the next request:

```php
$data->flash('success', 'Saved');
$data->peekFlashes('success'); // keeps messages
$data->getFlashes('success');  // reads and removes messages
```

## Session Identity

```php
use Componenta\Session\SessionId;

$id = new SessionId('abc123');
(string) $id; // abc123
```

Empty ids are rejected. `SessionIdGenerator` creates new ids for fresh and regenerated sessions.

## Lifecycle Manager

`SessionManager` coordinates storage and serialization.

```php
use Componenta\Session\SessionManager;

$manager = new SessionManager($storage);

$session = $manager->start($id);
$session->data->set('user_id', 42);

$manager->save($session);
$manager->regenerate($session, deleteOld: true);
$manager->destroy($session);
```

`start(null)` creates a new session. `start($id)` reads existing storage and hydrates metadata/data. Broken serialized payloads surface as `SessionException`.

## Storage And Serialization

Storage is abstracted by `SessionStorageInterface`:

- `ArraySessionStorage`: in-memory storage for simple runtime/test use.
- `FileSessionStorage`: file-backed storage.
- `NativeSessionHandler`: bridge to native PHP session handling.

Serialization is abstracted by `SessionSerializerInterface`:

- `PhpSerializer`
- `JsonSerializer`

Use JSON only for data that can be represented safely as JSON arrays/scalars.

## Cookies And Middleware

`SessionCookieManager` reads and writes session cookies using `SessionCookieOptions` and `SameSite`.

`SessionMiddleware` integrates the manager with a PSR-15 pipeline: it reads the request cookie, starts the session, attaches session state, delegates to the next handler, then persists the session and emits the cookie.

## Configuration

`SessionConfig` controls TTL and garbage-collection settings. Storage implementations may use these values differently, but the manager always passes TTL on save and `gcMaxLifetime` on cleanup.
