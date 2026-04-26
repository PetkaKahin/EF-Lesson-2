<?php

declare(strict_types=1);

namespace Task3\Contracts;

use DateTimeImmutable;

interface ClockInterface
{
    public function now(): DateTimeImmutable;
}