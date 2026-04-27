<?php

declare(strict_types=1);

namespace Task4\Domain\Contracts;

interface LoggerInterface
{
    public function log(string $message): void;
}