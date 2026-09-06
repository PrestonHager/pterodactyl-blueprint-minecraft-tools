<?php

namespace PhpOption;

class None extends Option
{
    public static function create(): self
    {
        return new self();
    }

    public function get($default = null)
    {
        return is_callable($default) ? $default() : $default;
    }

    public function getOrElse($default)
    {
        return is_callable($default) ? $default() : $default;
    }

    public function getOrThrow(\Exception $ex = null)
    {
        throw $ex ?: new \RuntimeException('Option is None');
    }

    public function isDefined(): bool
    {
        return false;
    }

    public function isEmpty(): bool
    {
        return true;
    }

    public function orElse($default)
    {
        return is_callable($default) ? $default() : $default;
    }

    public function map(callable $callback)
    {
        return $this;
    }

    public function flatMap(callable $callback)
    {
        return $this;
    }

    public function bind(callable $callback)
    {
        return $this;
    }

    public function filter(callable $callback)
    {
        return $this;
    }

    public function filterNot(callable $callback)
    {
        return $this;
    }

    public function present()
    {
        return $this;
    }

    public function toBool(): bool
    {
        return false;
    }

    public function toArray(): array
    {
        return [];
    }

    public function __toString(): string
    {
        return 'None';
    }
}
