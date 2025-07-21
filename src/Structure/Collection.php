<?php

declare(strict_types=1);

namespace Kosmos\Bitrix\DB\Structure;

use Closure;
use ReturnTypeWillChange;

/**
 * @noinspection all
 */
abstract class Collection implements \ArrayAccess, \Iterator, \Countable
{
    protected array $values;

    public function __construct()
    {
        $this->values = [];
    }

    #[ReturnTypeWillChange]
    public function current()
    {
        return current($this->values);
    }

    public function next(): void
    {
        next($this->values);
    }

    #[ReturnTypeWillChange]
    public function key()
    {
        return key($this->values);
    }

    public function valid(): bool
    {
        return ($this->key() !== null);
    }

    public function rewind(): void
    {
        reset($this->values);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->values[$offset]) || array_key_exists($offset, $this->values);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (isset($this->values[$offset]) || array_key_exists($offset, $this->values)) {
            return $this->values[$offset];
        }

        return null;
    }

    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $this->values[] = $value;
        } else {
            $this->values[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->values[$offset]);
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function isEmpty(): bool
    {
        return empty($this->values);
    }

    public function with(string $field, mixed $value): self
    {
        $collection = new static();

        if (empty($this->values)) {
            return $collection;
        }

        $method = null;
        $formattedField = ucfirst($field);
        if (is_bool($value)) {
            $method = 'is' . $formattedField;
        }

        $this->rewind();

        if (!$method || !method_exists($this->current(), $method)) {
            $method = 'get' . $formattedField;
        }

        if (!method_exists($this->current(), $method)) {
            return $collection;
        }

        foreach ($this->values as $obj) {
            if ($obj->$method() !== $value) {
                continue;
            }

            $collection->add($obj);
        }

        return $collection;
    }

    /**
     * @param int $mode [optional] <p>
     * Flag determining what arguments are sent to <i>callback</i>:
     * </p><ul>
     * <li>
     * <b>ARRAY_FILTER_USE_KEY</b> - pass key as the only argument
     * to <i>callback</i> instead of the value</span>
     * </li>
     * <li>
     * <b>ARRAY_FILTER_USE_BOTH</b> - pass both value and key as
     * arguments to <i>callback</i> instead of the value</span>
     * </li>
     * </ul>
     */
    public function filter(Closure $closure, int $mode = 0): self
    {
        $collection = new static();

        if (empty($this->values)) {
            return $collection;
        }

        $items = array_filter($this->values, $closure, $mode);

        foreach ($items as $item) {
            $collection->add($item);
        }

        return $collection;
    }

    public function asArray(): array
    {
        return $this->values;
    }

    public function first()
    {
        return $this->values[array_key_first($this->values)];
    }

    public function last()
    {
        return $this->values[array_key_last($this->values)];
    }
}
