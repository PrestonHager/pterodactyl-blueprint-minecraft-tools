<?php

namespace PhpOption;

abstract class Option implements \IteratorAggregate
{
    abstract public function get($default = null);
    abstract public function isDefined(): bool;
    abstract public function isEmpty(): bool;
    abstract public function map(callable $callback): Option;
    abstract public function flatMap(callable $callback): Option;
    abstract public function filter(callable $callback): Option;
    abstract public function filterNot(callable $callback): Option;
    abstract public function orElse($default): Option;
    abstract public function getOrElse($default);
    abstract public function getOrThrow(\Exception $ex = null);
    abstract public function present(): Option;
    abstract public function toBool(): bool;
    abstract public function toArray(): array;

    public function getIterator(): \Traversable
    {
        if ($this->isEmpty()) {
            return new \ArrayIterator([]);
        }
        return new \ArrayIterator([$this->get()]);
    }

    public static function fromValue($value, $noneValue = '__NONE__'): Option
    {
        if ($value === $noneValue) {
            return None::create();
        }
        return new Some($value);
    }

    public static function none(): Option
    {
        return None::create();
    }

    public static function some(mixed $value): Some
    {
        return new Some($value);
    }
}
