<?php

declare(strict_types=1);

namespace Task3\Contracts;

interface LoggerInterface
{
    public function log(string $message): void;
}