<?php

declare(strict_types=1);

namespace Task4\Domain\Contracts;

use DateTimeImmutable;

interface ClockInterface
{
    public function now(): DateTimeImmutable;
}