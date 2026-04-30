<?php

declare(strict_types=1);

namespace Task6\Infrastructure\Notification;

use Task6\Domain\Contracts\NotifyInterface;
use Task6\Domain\Order;

final class EchoLogger implements NotifyInterface
{
    public function send(Order $order): void
    {
        echo "[{$order->createdAt->format('Y-m-d H:i:s')}] order $order->id created";
    }
}
