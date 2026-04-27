<?php

declare(strict_types=1);

namespace Task4\Infrastructure;

use Task4\Domain\Contracts\LoggerInterface;

final class EchoLogger implements LoggerInterface
{
    public function log(string $message): void
    {
        echo $message;
    }
}
