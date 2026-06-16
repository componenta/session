# Componenta Session

Пакет управления сессиями для PHP 8.4+. Он даёт идентификатор сессии, данные сессии, метаданные, хранилище, сериализацию, cookie, менеджер жизненного цикла и PSR-15 промежуточный обработчик.

Используйте пакет, когда приложению нужен явный контроль сессий вместо прямой зависимости от глобального `$_SESSION`.

## Установка

```bash
composer require componenta/session
```

## Требования

- PHP 8.4+
- PSR-7 / PSR-15, если используется промежуточный обработчик

## Связанные пакеты

| Пакет | Зачем нужен здесь |
|---|---|
| `componenta/auth` | Использует идентификатор сессии для сессионной аутентификации. |
| `psr/http-message` / `psr/http-server-middleware` | Промежуточный обработчик сессии читает cookie из PSR-7 запроса и пишет cookie в PSR-7 ответ. |
| `componenta/pipeline` | Выполняет `SessionMiddleware` в HTTP-конвейере. |

## Данные сессии

`SessionData` хранит атрибуты и flash-сообщения.

```php
use Componenta\Session\SessionData;

$data = new SessionData(['user_id' => 42]);

$data->getInt('user_id');     // 42
$data->set('theme', 'dark');
$data->pull('theme');         // "dark" и удаление значения
$data->only('user_id');
$data->except('csrf_token');
```

Типизированные getters бросают `AttributeNotFoundException`, когда обязательный ключ отсутствует, и `AttributeTypeMismatchException`, когда значение имеет неправильный тип.

Flash-сообщения рассчитаны на следующий запрос:

```php
$data->flash('success', 'Saved');
$data->peekFlashes('success'); // прочитать, но оставить
$data->getFlashes('success');  // прочитать и удалить
```

## Идентификатор сессии

```php
use Componenta\Session\SessionId;

$id = new SessionId('abc123');
(string) $id; // abc123
```

Пустые id отклоняются. `SessionIdGenerator` создаёт новые id для свежих и обновлённых сессий.

## Менеджер жизненного цикла

`SessionManager` координирует хранилище и сериализацию.

```php
use Componenta\Session\SessionManager;

$manager = new SessionManager($storage);

$session = $manager->start($id);
$session->data->set('user_id', 42);

$manager->save($session);
$manager->regenerate($session, deleteOld: true);
$manager->destroy($session);
```

`start(null)` создаёт новую сессию. `start($id)` читает существующее хранилище и восстанавливает метаданные и данные. Повреждённые сериализованные данные проявляются как `SessionException`.

## Хранилище и сериализация

Хранилище абстрагировано через `SessionStorageInterface`:

- `ArraySessionStorage`: хранение в памяти для простых сценариев и тестов;
- `FileSessionStorage`: хранение в файлах;
- `NativeSessionHandler`: мост к нативному session handling PHP.

Сериализация абстрагирована через `SessionSerializerInterface`:

- `PhpSerializer`
- `JsonSerializer`

Используйте JSON только для данных, которые безопасно представлены как JSON-массивы и скаляры.

## Cookie и промежуточный обработчик

`SessionCookieManager` читает и пишет session cookie через `SessionCookieOptions` и `SameSite`.

`SessionMiddleware` интегрирует менеджер с PSR-15 конвейером: читает cookie запроса, стартует сессию, прикрепляет состояние сессии, делегирует следующему обработчику, затем сохраняет сессию и записывает cookie в ответ.

## Конфигурация

`SessionConfig` управляет TTL и настройками сборки мусора. Реализации хранилища могут использовать эти значения по-разному, но менеджер всегда передаёт TTL при `save()` и `gcMaxLifetime` при cleanup.
