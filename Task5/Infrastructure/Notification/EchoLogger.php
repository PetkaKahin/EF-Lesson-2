<?php

namespace Task5\Infrastructure\Notification;

use Task5\Domain\Order;
use Task5\Domain\Contracts\NotifyInterface;

class EchoLogger implements NotifyInterface
{
    public function send(Order $order): void
    {
        echo "[{$order->createdAt->format('Y-m-d H:i:s')}] order $order->id created";
    }
}