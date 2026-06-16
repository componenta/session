<?php

declare(strict_types=1);

namespace Componenta\Session;

use Componenta\Session\Exception\AttributeNotFoundException;
use Componenta\Session\Exception\AttributeTypeMismatchException;
use Componenta\Stdlib\DefaultValue;

final class SessionData implements \Countable, \IteratorAggregate
{
    private const string ATTRIBUTES_KEY = '_attributes';
    private const string FLASH_KEY = '_flash';

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, list<string>> $flashCurrent Messages available in current request
     * @param array<string, list<string>> $flashNew Messages added during current request
     */
    public function __construct(
        private array $attributes = [],
        private array $flashCurrent = [],
        private array $flashNew = [],
    ) {}

    // === CRUD ===

    public function get(string $key, mixed $default = DefaultValue::None): mixed
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        if ($default === DefaultValue::None) {
            throw AttributeNotFoundException::forKey($key);
        }

        return $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    public function remove(string $key): void
    {
        unset($this->attributes[$key]);
    }

    public function clear(): void
    {
        $this->attributes = [];
        $this->flashCurrent = [];
        $this->flashNew = [];
    }

    // === Typed Getters ===

    public function getString(string $key, ?string $default = null): string
    {
        $value = $this->getTyped($key, $default);

        if (!is_string($value)) {
            throw AttributeTypeMismatchException::expectedType($key, 'string', get_debug_type($value));
        }

        return $value;
    }

    public function getInt(string $key, ?int $default = null): int
    {
        $value = $this->getTyped($key, $default);

        if (!is_int($value)) {
            throw AttributeTypeMismatchException::expectedType($key, 'int', get_debug_type($value));
        }

        return $value;
    }

    public function getBool(string $key, ?bool $default = null): bool
    {
        $value = $this->getTyped($key, $default);

        if (!is_bool($value)) {
            throw AttributeTypeMismatchException::expectedType($key, 'bool', get_debug_type($value));
        }

        return $value;
    }

    public function getFloat(string $key, ?float $default = null): float
    {
        $value = $this->getTyped($key, $default);

        if (!is_float($value) && !is_int($value)) {
            throw AttributeTypeMismatchException::expectedType($key, 'float', get_debug_type($value));
        }

        return (float) $value;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getArray(string $key, ?array $default = null): array
    {
        $value = $this->getTyped($key, $default);

        if (!is_array($value)) {
            throw AttributeTypeMismatchException::expectedType($key, 'array', get_debug_type($value));
        }

        return $value;
    }

    // === Arrays ===

    public function push(string $key, mixed $value): void
    {
        if (!$this->has($key)) {
            $this->attributes[$key] = [];
        }

        if (!is_array($this->attributes[$key])) {
            throw AttributeTypeMismatchException::expectedType($key, 'array', get_debug_type($this->attributes[$key]));
        }

        $this->attributes[$key][] = $value;
    }

    // === Filtering ===

    /**
     * @return array<string, mixed>
     */
    public function only(string ...$keys): array
    {
        return array_intersect_key($this->attributes, array_flip($keys));
    }

    /**
     * @return array<string, mixed>
     */
    public function except(string ...$keys): array
    {
        return array_diff_key($this->attributes, array_flip($keys));
    }

    // === Numbers ===

    public function increment(string $key, int $step = 1): int
    {
        $value = $this->has($key) ? $this->getInt($key) : 0;
        $value += $step;
        $this->set($key, $value);

        return $value;
    }

    public function decrement(string $key, int $step = 1): int
    {
        return $this->increment($key, -$step);
    }

    // === Pull / Remember ===

    public function pull(string $key, mixed $default = DefaultValue::None): mixed
    {
        $value = $this->get($key, $default);
        $this->remove($key);

        return $value;
    }

    /**
     * @param callable(): mixed $callback
     */
    public function remember(string $key, callable $callback): mixed
    {
        if ($this->has($key)) {
            return $this->attributes[$key];
        }

        $value = $callback();
        $this->set($key, $value);

        return $value;
    }

    // === Flash ===

    public function flash(string $type, string $message): void
    {
        $this->flashNew[$type] ??= [];
        $this->flashNew[$type][] = $message;
    }

    /**
     * @param list<string> $messages
     */
    public function flashMany(string $type, array $messages): void
    {
        foreach ($messages as $message) {
            $this->flash($type, $message);
        }
    }

    public function reflash(): void
    {
        foreach ($this->flashCurrent as $type => $messages) {
            $this->flashNew[$type] ??= [];
            $this->flashNew[$type] = array_merge($this->flashNew[$type], $messages);
        }
        $this->flashCurrent = [];
    }

    /**
     * @return list<string>
     */
    public function getFlashes(string $type): array
    {
        $messages = $this->peekFlashes($type);
        unset($this->flashCurrent[$type]);

        return $messages;
    }

    /**
     * @return list<string>
     */
    public function peekFlashes(string $type): array
    {
        $messages = [];

        if (isset($this->flashCurrent[$type])) {
            $messages = array_merge($messages, $this->flashCurrent[$type]);
        }

        if (isset($this->flashNew[$type])) {
            $messages = array_merge($messages, $this->flashNew[$type]);
        }

        return $messages;
    }

    /**
     * @return array<string, list<string>>
     */
    public function getAllFlashes(): array
    {
        $all = $this->peekAllFlashes();
        $this->flashCurrent = [];

        return $all;
    }

    /**
     * @return array<string, list<string>>
     */
    public function peekAllFlashes(): array
    {
        $result = $this->flashCurrent;

        foreach ($this->flashNew as $type => $messages) {
            $result[$type] ??= [];
            $result[$type] = array_merge($result[$type], $messages);
        }

        return $result;
    }

    public function hasFlashes(string $type): bool
    {
        return isset($this->flashCurrent[$type]) || isset($this->flashNew[$type]);
    }

    public function clearFlashes(): void
    {
        $this->flashCurrent = [];
        $this->flashNew = [];
    }

    // === Countable / IteratorAggregate ===

    public function count(): int
    {
        return count($this->attributes);
    }

    public function getIterator(): \Generator
    {
        yield from $this->attributes;
    }

    // === Serialization ===

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->attributes;
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys($this->attributes);
    }

    public function isEmpty(): bool
    {
        return $this->attributes === [] && $this->flashCurrent === [] && $this->flashNew === [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            self::ATTRIBUTES_KEY => $this->attributes,
            self::FLASH_KEY => $this->flashNew,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data[self::ATTRIBUTES_KEY] ?? [],
            $data[self::FLASH_KEY] ?? [],
            [],
        );
    }

    private function getTyped(string $key, mixed $default): mixed
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        if ($default === null) {
            throw AttributeNotFoundException::forKey($key);
        }

        return $default;
    }
}
