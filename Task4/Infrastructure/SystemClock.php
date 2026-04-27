<?php

declare(strict_types=1);

namespace Task4\Infrastructure;

use DateTimeImmutable;
use Task4\Domain\Contracts\ClockInterface;

final class SystemClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
