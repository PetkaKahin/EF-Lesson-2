<?php

declare(strict_types=1);

namespace Task3\Infrastructure;

use Task3\Contracts\LoggerInterface;

final class EchoLogger implements LoggerInterface
{
    public function log(string $message): void
    {
        echo $message;
    }
}
