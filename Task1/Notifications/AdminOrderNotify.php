<?php

declare(strict_types=1);

namespace Task1\Notifications;

use Task1\Contracts\NotifyInterface;
use Task1\Order;

class AdminOrderNotify implements NotifyInterface
{
    public function __construct(
        private readonly string $email,
        private bool $debug,
    ) {}

    public function send(Order $order): void
    {
        $msg = "New order {$order->id} total={$order->pricing->total} customer={$order->customer->email}";
        if ($this->debug) {
            error_log("[MAIL to {$this->email}] {$msg}");
        }
    }
}