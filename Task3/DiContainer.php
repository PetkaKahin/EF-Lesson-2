<?php

namespace Task3;

use Closure;
use InvalidArgumentException;

class DiContainer
{
    private array $factories = [];
    private array $instances = [];
    private array $singletonKeys = [];

    public function bind(string $abstract, Closure $factory): void
    {
        $this->instances[$abstract] = $factory;
    }

    public function singleton(string $abstract, Closure $factory): void
    {
        $this->factories[$abstract] = $factory;
        $this->singletonKeys[$abstract] = true;
    }

    public function make(string $abstract): object
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (!isset($this->factories[$abstract])) {
            throw new InvalidArgumentException("Биндинг $abstract не зарегистрирован");
        }

        $object = $this->factories[$abstract]();

        if (isset($this->singletonKeys[$abstract])) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }
}