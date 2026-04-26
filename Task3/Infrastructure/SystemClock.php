<?php

declare(strict_types=1);

namespace Task3\Infrastructure;

use DateTimeImmutable;
use Task3\Contracts\ClockInterface;

final class SystemClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
