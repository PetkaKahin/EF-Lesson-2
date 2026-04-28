<?php

namespace Task5\Legacy;

final class ServiceLocator
{
    private static array $services = [];

    private function __construct() {}
    private function __clone() {}

    public static function set(string $id, mixed $service): void
    {
        self::$services[$id] = $service;
    }

    public static function setFactory(string $id, callable $factory): void
    {
        self::$services[$id] = $factory;
    }

    public static function get(string $id): mixed
    {
        if (!array_key_exists($id, self::$services)) {
            throw new \RuntimeException("Service '{$id}' not found in ServiceLocator");
        }

        $value = self::$services[$id];

        if (is_callable($value)) {
            $instance = $value();
            self::$services[$id] = $instance;
            return $instance;
        }

        return $value;
    }

    public static function has(string $id): bool
    {
        return array_key_exists($id, self::$services);
    }

    public static function reset(): void
    {
        self::$services = [];
    }
}