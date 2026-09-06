<?php

namespace PhpOption;

class Some extends Option
{
    private mixed $value;

    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    public static function create(mixed $value): self
    {
        return new self($value);
    }

    public function get($default = null): mixed
    {
        return $this->value;
    }

    public function getOrElse($default): mixed
    {
        return $this->value;
    }

    public function getOrThrow(\Exception $ex = null): mixed
    {
        return $this->value;
    }

    public function isDefined(): bool
    {
        return true;
    }

    public function isEmpty(): bool
    {
        return false;
    }

    public function orElse($default): Option
    {
        return $this;
    }

    public function map(callable $callback): Option
    {
        $result = $callback($this->value);
        return $result === null ? None::create() : new self($result);
    }

    public function flatMap(callable $callback): Option
    {
        return $callback($this->value);
    }

    public function bind(callable $callback): Option
    {
        return $callback($this->value);
    }

    public function filter(callable $callback): Option
    {
        return $callback($this->value) ? $this : None::create();
    }

    public function filterNot(callable $callback): Option
    {
        return !$callback($this->value) ? $this : None::create();
    }

    public function present(): Option
    {
        return $this;
    }

    public function toBool(): bool
    {
        return true;
    }

    public function toArray(): array
    {
        return [$this->value];
    }

    public function __toString(): string
    {
        return 'Some(' . var_export($this->value, true) . ')';
    }
}
